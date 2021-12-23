<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\ModelDescriber;

use Doctrine\Common\Annotations\Reader;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Exception\UndocumentedArrayItemsException;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\Annotations\AnnotationsReader;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\PropertyDescriber\PropertyDescriberInterface;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class ObjectModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;
    use ApplyOpenApiDiscriminatorTrait;

    /** @var PropertyInfoExtractorInterface */
    private $propertyInfo;
    /** @var Reader */
    private $doctrineReader;
    /** @var PropertyDescriberInterface[] */
    private $propertyDescribers;
    /** @var string[] */
    private $mediaTypes;
    /** @var NameConverterInterface[] */
    private $nameConverter;

    public function __construct(
        PropertyInfoExtractorInterface $propertyInfo,
        Reader $reader,
        iterable $propertyDescribers,
        array $mediaTypes,
        NameConverterInterface $nameConverter = null
    ) {
        $this->propertyInfo = $propertyInfo;
        $this->doctrineReader = $reader;
        $this->propertyDescribers = $propertyDescribers;
        $this->mediaTypes = $mediaTypes;
        $this->nameConverter = $nameConverter;
    }

    public function describe(Model $model, OA\Schema $schema)
    {
        $schema->type = 'object';

        $class = $model->getType()->getClassName();
        $schema->_context->class = $class;

        $context = ['serializer_groups' => null];
        if (null !== $model->getGroups()) {
            $context['serializer_groups'] = array_filter($model->getGroups(), 'is_string');
        }

        $reflClass = new \ReflectionClass($class);
        $annotationsReader = new AnnotationsReader($this->doctrineReader, $this->modelRegistry, $this->mediaTypes);
        $annotationsReader->updateDefinition($reflClass, $schema);

        $discriminatorMap = $this->getAnnotation($reflClass, DiscriminatorMap::class);
        if ($discriminatorMap && Generator::UNDEFINED === $schema->discriminator) {
            $this->applyOpenApiDiscriminator(
                $model,
                $schema,
                $this->modelRegistry,
                $discriminatorMap->getTypeProperty(),
                $discriminatorMap->getMapping()
            );
        }

        $propertyInfoProperties = $this->propertyInfo->getProperties($class, $context);

        if (null === $propertyInfoProperties) {
            return;
        }

        // Fix for https://github.com/nelmio/NelmioApiDocBundle/issues/1756
        // The SerializerExtractor does expose private/protected properties for some reason, so we eliminate them here
        $propertyInfoProperties = array_intersect($propertyInfoProperties, $this->propertyInfo->getProperties($class, []) ?? []);

        foreach ($propertyInfoProperties as $propertyName) {
            $serializedName = null !== $this->nameConverter ? $this->nameConverter->normalize($propertyName, $class, null, null !== $model->getGroups() ? ['groups' => $model->getGroups()] : []) : $propertyName;

            $reflections = $this->getReflections($reflClass, $propertyName);

            // Check if a custom name is set
            foreach ($reflections as $reflection) {
                $serializedName = $annotationsReader->getPropertyName($reflection, $serializedName);
            }

            $property = Util::getProperty($schema, $serializedName);

            // Interpret additional options
            $groups = $model->getGroups();
            if (isset($groups[$propertyName]) && is_array($groups[$propertyName])) {
                $groups = $model->getGroups()[$propertyName];
            }
            foreach ($reflections as $reflection) {
                $annotationsReader->updateProperty($reflection, $property, $groups);
            }

            // If type manually defined
            if (Generator::UNDEFINED !== $property->type || Generator::UNDEFINED !== $property->ref) {
                continue;
            }

            $types = $this->propertyInfo->getTypes($class, $propertyName);
            if (null === $types || 0 === count($types)) {
                throw new \LogicException(sprintf('The PropertyInfo component was not able to guess the type of %s::$%s. You may need to add a `@var` annotation or use `@OA\Property(type="")` to make its type explicit.', $class, $propertyName));
            }

            $this->describeProperty($types, $model, $property, $propertyName);
        }
    }

    /**
     * @return \ReflectionProperty[]|\ReflectionMethod[]
     */
    private function getReflections(\ReflectionClass $reflClass, string $propertyName): array
    {
        $reflections = [];
        if ($reflClass->hasProperty($propertyName)) {
            $reflections[] = $reflClass->getProperty($propertyName);
        }

        $camelProp = $this->camelize($propertyName);
        foreach (['', 'get', 'is', 'has', 'can', 'add', 'remove', 'set'] as $prefix) {
            if ($reflClass->hasMethod($prefix.$camelProp)) {
                $reflections[] = $reflClass->getMethod($prefix.$camelProp);
            }
        }

        return $reflections;
    }

    /**
     * Camelizes a given string.
     */
    private function camelize(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    /**
     * @param Type[] $types
     */
    private function describeProperty(array $types, Model $model, OA\Schema $property, string $propertyName)
    {
        foreach ($this->propertyDescribers as $propertyDescriber) {
            if ($propertyDescriber instanceof ModelRegistryAwareInterface) {
                $propertyDescriber->setModelRegistry($this->modelRegistry);
            }
            if ($propertyDescriber->supports($types)) {
                try {
                    $propertyDescriber->describe($types, $property, $model->getGroups());
                } catch (UndocumentedArrayItemsException $e) {
                    if (null !== $e->getClass()) {
                        throw $e; // This exception is already complete
                    }

                    throw new UndocumentedArrayItemsException($model->getType()->getClassName(), sprintf('%s%s', $propertyName, $e->getPath()));
                }

                return;
            }
        }

        throw new \Exception(sprintf('Type "%s" is not supported in %s::$%s. You may use the `@OA\Property(type="")` annotation to specify it manually.', $types[0]->getBuiltinType(), $model->getType()->getClassName(), $propertyName));
    }

    /**
     * @return mixed
     */
    private function getAnnotation(\ReflectionClass $reflection, string $className)
    {
        if (false === class_exists($className)) {
            return null;
        }
        if (\PHP_VERSION_ID >= 80000) {
            if (null !== $attribute = $reflection->getAttributes($className, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? null) {
                return $attribute->newInstance();
            }
        }

        return $this->doctrineReader->getClassAnnotation($reflection, $className);
    }

    public function supports(Model $model): bool
    {
        return Type::BUILTIN_TYPE_OBJECT === $model->getType()->getBuiltinType() && class_exists($model->getType()->getClassName());
    }
}
