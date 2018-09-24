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
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\SerializationContext;
use Metadata\MetadataFactoryInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\Annotations\AnnotationsReader;
use Symfony\Component\PropertyInfo\Type;

/**
 * Uses the JMS metadata factory to extract input/output model information.
 */
class JMSModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    private $factory;
    private $namingStrategy;
    private $doctrineReader;
    private $previousGroups = [];

    /**
     * @var array
     */
    private $propertyTypeUseGroupsCache = [];

    public function __construct(
        MetadataFactoryInterface $factory,
        PropertyNamingStrategyInterface $namingStrategy,
        Reader $reader
    ) {
        $this->factory = $factory;
        $this->namingStrategy = $namingStrategy;
        $this->doctrineReader = $reader;
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
        $annotationsReader = new AnnotationsReader($this->doctrineReader, $this->modelRegistry);
        $annotationsReader->updateDefinition(new \ReflectionClass($className), $schema);

        $properties = $schema->getProperties();
        foreach ($metadata->propertyMetadata as $item) {
            // filter groups
            if (null !== $groupsExclusion && $groupsExclusion->shouldSkipProperty($item, SerializationContext::create())) {
                continue;
            }

            $name = $this->namingStrategy->translateName($item);
            $groups = $model->getGroups();

            $previousGroups = null;
            if (isset($groups[$name]) && is_array($groups[$name])) {
                $previousGroups = $groups;
                $groups = $model->getGroups()[$name];
            } elseif (!isset($groups[$name]) && !empty($this->previousGroups[spl_object_hash($model)])) {
                // $groups = $this->previousGroups[spl_object_hash($model)]; use this for jms/serializer 2.0
                $groups = false === $this->propertyTypeUsesGroups($item->type) ? null : [GroupsExclusionStrategy::DEFAULT_GROUP];
            } elseif (is_array($groups)) {
                $groups = array_filter($groups, 'is_scalar');
            }

            if ([GroupsExclusionStrategy::DEFAULT_GROUP] === $groups) {
                $groups = null;
            }

            // read property options from Swagger Property annotation if it exists
            if (null !== $item->reflection) {
                $property = $properties->get($annotationsReader->getPropertyName($item->reflection, $name));
                $annotationsReader->updateProperty($item->reflection, $property, $groups);
            } else {
                $property = $properties->get($name);
            }

            if (null !== $property->getType() || null !== $property->getRef()) {
                continue;
            }
            if (null === $item->type) {
                $properties->remove($name);

                continue;
            }

            $this->describeItem($item->type, $property, $groups, $previousGroups);
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

    private function describeItem(array $type, $property, array $groups = null, array $previousGroups = null)
    {
        $nestedTypeInfo = $this->getNestedTypeInArray($type);
        if (null !== $nestedTypeInfo) {
            list($nestedType, $isHash) = $nestedTypeInfo;
            if ($isHash) {
                $property->setType('object');
                // in the case of a virtual property, set it as free object type
                $property->merge(['additionalProperties' => []]);

                // this is a free form object (as nested array)
                if ('array' === $nestedType['name'] && !isset($nestedType['params'][0])) {
                    return;
                }

                $this->describeItem($nestedType, $property->getAdditionalProperties(), $groups, $previousGroups);

                return;
            }

            $property->setType('array');
            $this->describeItem($nestedType, $property->getItems(), $groups);
        } elseif ('array' === $type['name']) {
            $property->setType('object');
            $property->merge(['additionalProperties' => []]);
        } elseif (in_array($type['name'], ['boolean', 'string'], true)) {
            $property->setType($type['name']);
        } elseif (in_array($type['name'], ['int', 'integer'], true)) {
            $property->setType('integer');
        } elseif (in_array($type['name'], ['double', 'float'], true)) {
            $property->setType('number');
            $property->setFormat($type['name']);
        } elseif (is_subclass_of($type['name'], \DateTimeInterface::class)) {
            $property->setType('string');
            $property->setFormat('date-time');
        } else {
            // we can use property type also for custom handlers, then we don't have here real class name
            if (!class_exists($type['name'])) {
                return null;
            }

            $property->setRef($this->modelRegistry->register(
                new Model(new Type(Type::BUILTIN_TYPE_OBJECT, false, $type['name']), $groups)
            ));

            if ($previousGroups) {
                $this->previousGroups[spl_object_hash($model)] = $previousGroups;
            }
        }
    }

    private function getNestedTypeInArray(array $type)
    {
        if ('array' !== $type['name'] && 'ArrayCollection' !== $type['name']) {
            return null;
        }
        // array<string, MyNamespaceMyObject>
        if (isset($type['params'][1]['name'])) {
            return [$type['params'][1], true];
        }
        // array<MyNamespaceMyObject>
        if (isset($type['params'][0]['name'])) {
            return [$type['params'][0], false];
        }

        return null;
    }

    /**
     * @param array $type
     *
     * @return bool|null
     */
    private function propertyTypeUsesGroups(array $type)
    {
        if (array_key_exists($type['name'], $this->propertyTypeUseGroupsCache)) {
            return $this->propertyTypeUseGroupsCache[$type['name']];
        }

        try {
            $metadata = $this->factory->getMetadataForClass($type['name']);

            foreach ($metadata->propertyMetadata as $item) {
                if (null !== $item->groups && $item->groups != [GroupsExclusionStrategy::DEFAULT_GROUP]) {
                    $this->propertyTypeUseGroupsCache[$type['name']] = true;

                    return true;
                }
            }
            $this->propertyTypeUseGroupsCache[$type['name']] = false;

            return false;
        } catch (\ReflectionException $e) {
            $this->propertyTypeUseGroupsCache[$type['name']] = null;

            return null;
        }
    }
}
