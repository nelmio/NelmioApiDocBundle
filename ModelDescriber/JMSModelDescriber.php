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
use JMS\Serializer\Context;
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

    private $contexts = [];

    private $metadataStacks = [];

    /**
     * @var array
     */
    private $propertyTypeUseGroupsCache = [];

    public function __construct(
        MetadataFactoryInterface $factory,
        PropertyNamingStrategyInterface $namingStrategy = null,
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

        $schema->setType('object');
        $annotationsReader = new AnnotationsReader($this->doctrineReader, $this->modelRegistry);
        $annotationsReader->updateDefinition(new \ReflectionClass($className), $schema);

        $isJmsV1 = null !== $this->namingStrategy;
        $properties = $schema->getProperties();

        $context = $this->getSerializationContext($model);
        $context->pushClassMetadata($metadata);
        foreach ($metadata->propertyMetadata as $item) {
            // filter groups
            if (null !== $context->getExclusionStrategy() && $context->getExclusionStrategy()->shouldSkipProperty($item, $context)) {
                continue;
            }

            $context->pushPropertyMetadata($item);

            $name = true === $isJmsV1 ? $this->namingStrategy->translateName($item) : $item->serializedName;
            // read property options from Swagger Property annotation if it exists
            try {
                if (true === $isJmsV1 && property_exists($item, 'reflection') && null !== $item->reflection) {
                    $reflection = $item->reflection;
                } else {
                    $reflection = new \ReflectionProperty($item->class, $item->name);
                }

                $property = $properties->get($annotationsReader->getPropertyName($reflection, $name));
                $groups = $this->computeGroups($context, $item->type);
                $annotationsReader->updateProperty($reflection, $property, $groups);
            } catch (\ReflectionException $e) {
                $property = $properties->get($name);
            }

            if (null !== $property->getType() || null !== $property->getRef()) {
                $context->popPropertyMetadata();

                continue;
            }
            if (null === $item->type) {
                $properties->remove($name);
                $context->popPropertyMetadata();

                continue;
            }

            $this->describeItem($item->type, $property, $context, $item);
            $context->popPropertyMetadata();
        }
        $context->popClassMetadata();
    }

    /**
     * @internal
     */
    public function getSerializationContext(Model $model): SerializationContext
    {
        if (isset($this->contexts[$model->getHash()])) {
            $context = $this->contexts[$model->getHash()];

            $stack = $context->getMetadataStack();
            while (!$stack->isEmpty()) {
                $stack->pop();
            }

            foreach ($this->metadataStacks[$model->getHash()] as $metadataCopy) {
                $stack->unshift($metadataCopy);
            }
        } else {
            $context = SerializationContext::create();

            if (null !== $model->getGroups()) {
                $context->addExclusionStrategy(new GroupsExclusionStrategy($model->getGroups()));
            }
        }

        return $context;
    }

    private function computeGroups(Context $context, array $type = null)
    {
        if (null === $type || true !== $this->propertyTypeUsesGroups($type)) {
            return null;
        }

        $groupsExclusion = $context->getExclusionStrategy();
        if (!($groupsExclusion instanceof GroupsExclusionStrategy)) {
            return null;
        }

        $groups = $groupsExclusion->getGroupsFor($context);
        if ([GroupsExclusionStrategy::DEFAULT_GROUP] === $groups) {
            return null;
        }

        return $groups;
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

    /**
     * @internal
     */
    public function describeItem(array $type, $property, Context $context)
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

                $this->describeItem($nestedType, $property->getAdditionalProperties(), $context);

                return;
            }

            $property->setType('array');
            $this->describeItem($nestedType, $property->getItems(), $context);
        } elseif ('array' === $type['name']) {
            $property->setType('object');
            $property->merge(['additionalProperties' => []]);
        } elseif ('string' === $type['name']) {
            $property->setType('string');
        } elseif (in_array($type['name'], ['bool', 'boolean'], true)) {
            $property->setType('boolean');
        } elseif (in_array($type['name'], ['int', 'integer'], true)) {
            $property->setType('integer');
        } elseif (in_array($type['name'], ['double', 'float'], true)) {
            $property->setType('number');
            $property->setFormat($type['name']);
        } elseif (is_subclass_of($type['name'], \DateTimeInterface::class)) {
            $property->setType('string');
            $property->setFormat('date-time');
        } else {
            $groups = $this->computeGroups($context, $type);

            $model = new Model(new Type(Type::BUILTIN_TYPE_OBJECT, false, $type['name']), $groups);
            $property->setRef($this->modelRegistry->register($model));

            $this->contexts[$model->getHash()] = $context;
            $this->metadataStacks[$model->getHash()] = clone $context->getMetadataStack();
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
