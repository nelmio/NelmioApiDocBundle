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

use EXSyst\Component\Swagger\Schema;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

class ObjectModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    private $propertyInfo;

    private $swaggerPropertyAnnotationReader;

    public function __construct(
        PropertyInfoExtractorInterface $propertyInfo,
        SwaggerPropertyAnnotationReader $swaggerPropertyAnnotationReader
    )
    {
        $this->propertyInfo = $propertyInfo;
        $this->swaggerPropertyAnnotationReader = $swaggerPropertyAnnotationReader;
    }

    public function describe(Model $model, Schema $schema)
    {
        $schema->setType('object');
        $properties = $schema->getProperties();

        $class = $model->getType()->getClassName();
        $context = [];
        if (null !== $model->getGroups()) {
            $context = ['serializer_groups' => $model->getGroups()];
        }

        $propertyInfoProperties = $this->propertyInfo->getProperties($class, $context);
        if (null === $propertyInfoProperties) {
            return;
        }

        foreach ($propertyInfoProperties as $propertyName) {
            $types = $this->propertyInfo->getTypes($class, $propertyName);
            if (0 === count($types)) {
                throw new \LogicException(sprintf('The PropertyInfo component was not able to guess the type of %s::$%s', $class, $propertyName));
            }
            if (count($types) > 1) {
                throw new \LogicException(sprintf('Property %s::$%s defines more than one type.', $class, $propertyName));
            }

            $type = $types[0];
            $property = $properties->get($propertyName);

            if (Type::BUILTIN_TYPE_ARRAY === $type->getBuiltinType()) {
                $type = $type->getCollectionValueType();
                $property->setType('array');
                $property = $property->getItems();
            }

            if ($type->getBuiltinType() === Type::BUILTIN_TYPE_STRING) {
                $property->setType('string');
            } elseif ($type->getBuiltinType() === Type::BUILTIN_TYPE_BOOL) {
                $property->setType('boolean');
            } elseif ($type->getBuiltinType() === Type::BUILTIN_TYPE_INT) {
                $property->setType('integer');
            } elseif ($type->getBuiltinType() === Type::BUILTIN_TYPE_FLOAT) {
                $property->setType('number');
                $property->setFormat('float');
            } elseif ($type->getBuiltinType() === Type::BUILTIN_TYPE_OBJECT) {
                if (in_array($type->getClassName(), ['DateTime', 'DateTimeImmutable'])) {
                    $property->setType('string');
                    $property->setFormat('date-time');
                } else {
                    $property->setRef(
                        $this->modelRegistry->register(new Model($type, $model->getGroups()))
                    );
                }
            } else {
                throw new \Exception(sprintf("Unknown type: %s", $type->getBuiltinType()));
            }

            // read property options from Swagger Property annotation if it exists
            if (property_exists($class, $propertyName)) {
                $this->swaggerPropertyAnnotationReader->updateWithSwaggerPropertyAnnotation(
                    new \ReflectionProperty($class, $propertyName),
                    $property
                );
            }
        }
    }

    public function supports(Model $model): bool
    {
        return Type::BUILTIN_TYPE_OBJECT === $model->getType()->getBuiltinType();
    }
}
