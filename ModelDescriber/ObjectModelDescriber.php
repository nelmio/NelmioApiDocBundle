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
use Swagger\Annotations\Property;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

class ObjectModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    private $propertyInfo;
    private $annotationReader;

    public function __construct(PropertyInfoExtractorInterface $propertyInfo, Reader $reader)
    {
        $this->propertyInfo = $propertyInfo;
        $this->annotationReader = $reader;
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

        $refClass = new \ReflectionClass($class);
        foreach ($propertyInfoProperties as $propertyName) {
            $annotation = null;
            if ($refClass->hasProperty($propertyName)) {
                $annotation = $this->annotationReader->getPropertyAnnotation($refClass->getProperty($propertyName), Property::class);
            }
            if ($annotation) {
                $properties->get($propertyName)->merge(json_decode(json_encode($annotation)));
            } else {
                $types = $this->propertyInfo->getTypes($class, $propertyName);
                if (0 === count($types)) {
                    throw new \LogicException(sprintf('The PropertyInfo component was not able to guess the type of %s::$%s', $class, $propertyName));
                }
                if (count($types) > 1) {
                    throw new \LogicException(sprintf('Property %s::$%s defines more than one type.', $class, $propertyName));
                }

                $properties->get($propertyName)->setRef(
                    $this->modelRegistry->register(new Model($types[0], $model->getGroups()))
                );
            }
        }
    }

    public function supports(Model $model): bool
    {
        return Type::BUILTIN_TYPE_OBJECT === $model->getType()->getBuiltinType();
    }
}
