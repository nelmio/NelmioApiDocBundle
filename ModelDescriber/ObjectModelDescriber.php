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
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\Annotations\AnnotationsReader;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\PropertyDescriber\PropertyDescriberInterface;
use OpenApi\Annotations as OA;
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
    /** @var string[] */
    private $mediaTypes;
    /** @var NameConverterInterface[] */
    private $nameConverter;

    private $swaggerDefinitionAnnotationReader;

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

        $context = [];
        if (null !== $model->getGroups()) {
            $context = ['serializer_groups' => array_filter($model->getGroups(), 'is_string')];
        }

        $annotationsReader = new AnnotationsReader($this->doctrineReader, $this->modelRegistry, $this->mediaTypes);
        $annotationsReader->updateDefinition(new \ReflectionClass($class), $schema);

        $propertyInfoProperties = $this->propertyInfo->getProperties($class, $context);
        if (null === $propertyInfoProperties) {
            return;
        }

        foreach ($propertyInfoProperties as $propertyName) {
            $serializedName = null !== $this->nameConverter ? $this->nameConverter->normalize($propertyName, $class, null, null !== $model->getGroups() ? ['groups' => $model->getGroups()] : []) : $propertyName;

            // read property options from OpenApi Property annotation if it exists
            if (property_exists($class, $propertyName)) {
                $reflectionProperty = new \ReflectionProperty($class, $propertyName);
                $property = Util::getProperty($schema, $annotationsReader->getPropertyName($reflectionProperty, $serializedName));

                $groups = $model->getGroups();
                if (isset($groups[$propertyName]) && is_array($groups[$propertyName])) {
                    $groups = $model->getGroups()[$propertyName];
                }

                $annotationsReader->updateProperty($reflectionProperty, $property, $groups);
            } else {
                $property = Util::getProperty($schema, $serializedName);
            }

            // If type manually defined
            if (OA\UNDEFINED !== $property->type || OA\UNDEFINED !== $property->ref) {
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
     * @param Type[] $types
     */
    private function describeProperty(array $types, Model $model, OA\Schema $property, string $propertyName)
    {
        foreach ($this->propertyDescribers as $propertyDescriber) {
            if ($propertyDescriber instanceof ModelRegistryAwareInterface) {
                $propertyDescriber->setModelRegistry($this->modelRegistry);
            }
            if ($propertyDescriber->supports($types)) {
                $propertyDescriber->describe($types, $property, $model->getGroups());

                return;
            }
        }

        throw new \Exception(sprintf('Type "%s" is not supported in %s::$%s. You may use the `@OA\Property(type="")` annotation to specify it manually.', $types[0]->getBuiltinType(), $model->getType()->getClassName(), $propertyName));
    }

    public function supports(Model $model): bool
    {
        return Type::BUILTIN_TYPE_OBJECT === $model->getType()->getBuiltinType() && class_exists($model->getType()->getClassName());
    }
}
