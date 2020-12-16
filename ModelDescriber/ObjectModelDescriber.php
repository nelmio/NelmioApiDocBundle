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
use EXSyst\Component\Swagger\Schema;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Exception\UndocumentedArrayItemsException;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\Annotations\AnnotationsReader;
use Nelmio\ApiDocBundle\PropertyDescriber\PropertyDescriberInterface;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class ObjectModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    /** @var PropertyInfoExtractorInterface */
    private $propertyInfo;
    /** @var Reader */
    private $doctrineReader;
    /** @var PropertyDescriberInterface[] */
    private $propertyDescribers;
    /** @var NameConverterInterface[] */
    private $nameConverter;

    private $swaggerDefinitionAnnotationReader;

    public function __construct(
        PropertyInfoExtractorInterface $propertyInfo,
        Reader $reader,
        $propertyDescribers,
        NameConverterInterface $nameConverter = null
    ) {
        $this->propertyInfo = $propertyInfo;
        $this->doctrineReader = $reader;
        $this->propertyDescribers = $propertyDescribers;
        $this->nameConverter = $nameConverter;
    }

    public function describe(Model $model, Schema $schema)
    {
        $schema->setType('object');
        $properties = $schema->getProperties();

        $class = $model->getType()->getClassName();
        $context = ['serializer_groups' => null]; // Use the SerializerExtractor with no groups check (sf >= 5.1)
        if (null !== $model->getGroups()) {
            $context['serializer_groups'] = array_filter($model->getGroups(), 'is_string');
        }

        $reflClass = new \ReflectionClass($class);

        $annotationsReader = new AnnotationsReader($this->doctrineReader, $this->modelRegistry);
        $annotationsReader->updateDefinition($reflClass, $schema);

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

            $property = $properties->get($serializedName);

            // Interpret additional options
            $groups = $model->getGroups();
            if (isset($groups[$propertyName]) && is_array($groups[$propertyName])) {
                $groups = $model->getGroups()[$propertyName];
            }
            foreach ($reflections as $reflection) {
                $annotationsReader->updateProperty($reflection, $property, $groups);
            }

            // If type manually defined
            if (null !== $property->getType() || null !== $property->getRef()) {
                continue;
            }

            $types = $this->propertyInfo->getTypes($class, $propertyName);
            if (null === $types || 0 === count($types)) {
                throw new \LogicException(sprintf('The PropertyInfo component was not able to guess the type of %s::$%s. You may need to add a `@var` annotation or use `@SWG\Property(type="")` to make its type explicit.', $class, $propertyName));
            }
            if (count($types) > 1) {
                throw new \LogicException(sprintf('Property %s::$%s defines more than one type. You can specify the one that should be documented using `@SWG\Property(type="")`.', $class, $propertyName));
            }

            $type = $types[0];
            $this->describeProperty($type, $model, $property, $propertyName);
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

    private function describeProperty(Type $type, Model $model, Schema $property, string $propertyName)
    {
        foreach ($this->propertyDescribers as $propertyDescriber) {
            if ($propertyDescriber instanceof ModelRegistryAwareInterface) {
                $propertyDescriber->setModelRegistry($this->modelRegistry);
            }
            if ($propertyDescriber->supports($type)) {
                try {
                    $propertyDescriber->describe($type, $property, $model->getGroups());
                } catch (UndocumentedArrayItemsException $e) {
                    if (null !== $e->getClass()) {
                        throw $e; // This exception is already complete
                    }

                    throw new UndocumentedArrayItemsException($model->getType()->getClassName(), sprintf('%s%s', $propertyName, $e->getPath()));
                }

                return;
            }
        }

        throw new \Exception(sprintf('Type "%s" is not supported in %s::$%s. You may use the `@SWG\Property(type="")` annotation to specify it manually.', $type->getBuiltinType(), $model->getType()->getClassName(), $propertyName));
    }

    public function supports(Model $model): bool
    {
        return Type::BUILTIN_TYPE_OBJECT === $model->getType()->getBuiltinType() && class_exists($model->getType()->getClassName());
    }
}
