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
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\SerializationContext;
use Metadata\MetadataFactoryInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use Symfony\Component\PropertyInfo\Type;

/**
 * Uses the JMS metadata factory to extract input/output model information.
 */
class JMSModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    private $factory;
    private $namingStrategy;

    public function __construct(MetadataFactoryInterface $factory, PropertyNamingStrategyInterface $namingStrategy)
    {
        $this->factory = $factory;
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function describe(Model $model, Schema $schema)
    {
        $className = $model->getType()->getClassName();
        $metadata = $this->factory->getMetadataForClass($className);
        if (null === $metadata) {
            throw new \InvalidArgumentException(sprintf('No metadata found for class %s.', $className));
        }

        $groupsExclusion = null !== $model->getGroups() ? new GroupsExclusionStrategy($model->getGroups()) : null;

        $schema->setType('object');
        $properties = $schema->getProperties();
        foreach ($metadata->propertyMetadata as $item) {
            if (null === $item->type) {
                continue;
            }

            // filter groups
            if (null !== $groupsExclusion && $groupsExclusion->shouldSkipProperty($item, SerializationContext::create())) {
                continue;
            }

            $name = $this->namingStrategy->translateName($item);
            $property = $properties->get($name);

            if ($type = $this->getNestedTypeInArray($item)) {
                $property->setType('array');
                $property = $property->getItems();
            } else {
                $type = $item->type['name'];
            }

            if (in_array($type, array('boolean', 'integer', 'string', ' float', 'array'))) {
                $property->setType($type);
            } elseif ('double' === $type) {
                $property->setType('float');
            } elseif ('DateTime' === $type || 'DateTimeImmutable' === $type) {
                $property->setType('string');
                $property->setFormat('date-time');
            } else {
                // we can use property type also for custom handlers, then we don't have here real class name
                if (!class_exists($type)) {
                    continue;
                }

                $property->setRef(
                    $this->modelRegistry->register(new Model(new Type(Type::BUILTIN_TYPE_OBJECT, false, $type), $model->getGroups()))
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $model): bool
    {
        $className = $model->getType()->getClassName();
        try {
            if ($this->factory->getMetadataForClass($className)) {
                return true;
            }
        } catch (\ReflectionException $e) {
        }

        return false;
    }

    private function getNestedTypeInArray(PropertyMetadata $item)
    {
        if ('array' !== $item->type['name'] && 'ArrayCollection' !== $item->type['name']) {
            return;
        }

        // array<string, MyNamespaceMyObject>
        if (isset($item->type['params'][1]['name'])) {
            return $item->type['params'][1]['name'];
        }

        // array<MyNamespaceMyObject>
        if (isset($item->type['params'][0]['name'])) {
            return $item->type['params'][0]['name'];
        }
    }
}
