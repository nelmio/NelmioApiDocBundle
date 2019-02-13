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
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\Annotations\AnnotationsReader;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

class ObjectModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    private $propertyInfo;
    private $doctrineReader;

    private $swaggerDefinitionAnnotationReader;

    public function __construct(
        PropertyInfoExtractorInterface $propertyInfo,
        Reader $reader
    ) {
        $this->propertyInfo = $propertyInfo;
        $this->doctrineReader = $reader;
    }

    public function describe(Model $model, Schema $schema)
    {
        $schema->setType('object');
        $properties = $schema->getProperties();

        $class = $model->getType()->getClassName();
        $context = [];
        if (null !== $model->getGroups()) {
            $context = ['serializer_groups' => array_filter($model->getGroups(), 'is_string')];
        }

        $annotationsReader = new AnnotationsReader($this->doctrineReader, $this->modelRegistry);
        $annotationsReader->updateDefinition(new \ReflectionClass($class), $schema);

        $propertyInfoProperties = $this->propertyInfo->getProperties($class, $context);
        if (null === $propertyInfoProperties) {
            return;
        }

        foreach ($propertyInfoProperties as $propertyName) {
            // read property options from Swagger Property annotation if it exists
            if (property_exists($class, $propertyName)) {
                $reflectionProperty = new \ReflectionProperty($class, $propertyName);
                $property = $properties->get($annotationsReader->getPropertyName($reflectionProperty, $propertyName));

                $groups = $model->getGroups();
                if (isset($groups[$propertyName]) && is_array($groups[$propertyName])) {
                    $groups = $model->getGroups()[$propertyName];
                }

                $annotationsReader->updateProperty($reflectionProperty, $property, $groups);
            } else {
                $property = $properties->get($propertyName);
            }

            // If type manually defined
            if (null !== $property->getType() || null !== $property->getRef()) {
                continue;
            }

            $types = $this->propertyInfo->getTypes($class, $propertyName);
            if (null === $types || 0 === count($types)) {
                throw new \LogicException(sprintf('The PropertyInfo component was not able to guess the type of %s::$%s', $class, $propertyName));
            }
            if (count($types) > 1) {
                throw new \LogicException(sprintf('Property %s::$%s defines more than one type.', $class, $propertyName));
            }

            $type = $types[0];
            if ($type->isCollection()) {
                $type = $type->getCollectionValueType();
                if (null === $type) {
                    throw new \LogicException(sprintf('Property "%s:%s" is an array, but no indication of the array elements are made. Use e.g. string[] for an array of string.', $class, $propertyName));
                }

                $property->setType('array');
                $property = $property->getItems();
            }

            if (Type::BUILTIN_TYPE_STRING === $type->getBuiltinType()) {
                $property->setType('string');
            } elseif (Type::BUILTIN_TYPE_BOOL === $type->getBuiltinType()) {
                $property->setType('boolean');
            } elseif (Type::BUILTIN_TYPE_INT === $type->getBuiltinType()) {
                $property->setType('integer');
            } elseif (Type::BUILTIN_TYPE_FLOAT === $type->getBuiltinType()) {
                $property->setType('number');
                $property->setFormat('float');
            } elseif (Type::BUILTIN_TYPE_OBJECT === $type->getBuiltinType()) {
                if (is_a($type->getClassName(), \DateTimeInterface::class, true)) {
                    $property->setType('string');
                    $property->setFormat('date-time');
                } else {
                    $type = new Type($type->getBuiltinType(), false, $type->getClassName(), $type->isCollection(), $type->getCollectionKeyType(), $type->getCollectionValueType()); // ignore nullable field

                    $property->setRef(
                        $this->modelRegistry->register(new Model($type, $model->getGroups()))
                    );
                }
            } else {
                throw new \Exception(sprintf('Type "%s" is not supported in %s::$%s. You may use the `@SWG\Property(type="")` annotation to specify it manually.', $type->getBuiltinType(), $class, $propertyName));
            }
        }
    }

    public function supports(Model $model): bool
    {
        return Type::BUILTIN_TYPE_OBJECT === $model->getType()->getBuiltinType();
    }
}
