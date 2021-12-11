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
use JMS\Serializer\Context;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\SerializationContext;
use Metadata\MetadataFactoryInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\Annotations\AnnotationsReader;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
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

    private $mediaTypes;

    /**
     * @var array
     */
    private $propertyTypeUseGroupsCache = [];

    public function __construct(
        MetadataFactoryInterface $factory,
        Reader $reader,
        array $mediaTypes,
        ?PropertyNamingStrategyInterface $namingStrategy = null
    ) {
        $this->factory = $factory;
        $this->namingStrategy = $namingStrategy;
        $this->doctrineReader = $reader;
        $this->mediaTypes = $mediaTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function describe(Model $model, OA\Schema $schema)
    {
        $className = $model->getType()->getClassName();
        $metadata = $this->factory->getMetadataForClass($className);
        if (null === $metadata) {
            throw new \InvalidArgumentException(sprintf('No metadata found for class %s.', $className));
        }

        $schema->type = 'object';
        $annotationsReader = new AnnotationsReader($this->doctrineReader, $this->modelRegistry, $this->mediaTypes);
        $annotationsReader->updateDefinition(new \ReflectionClass($className), $schema);

        $isJmsV1 = null !== $this->namingStrategy;

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

            $reflections = [];
            if (true === $isJmsV1 && property_exists($item, 'reflection') && null !== $item->reflection) {
                $reflections[] = $item->reflection;
            } elseif (\property_exists($item->class, $item->name)) {
                $reflections[] = new \ReflectionProperty($item->class, $item->name);
            }

            if (null !== $item->getter) {
                try {
                    $reflections[] = new \ReflectionMethod($item->class, $item->getter);
                } catch (\ReflectionException $ignored) {
                }
            }
            if (null !== $item->setter) {
                try {
                    $reflections[] = new \ReflectionMethod($item->class, $item->setter);
                } catch (\ReflectionException $ignored) {
                }
            }

            $groups = $this->computeGroups($context, $item->type);

            if (true === $item->inline && isset($item->type['name'])) {
                // currently array types can not be documented :-/
                if (!in_array($item->type['name'], ['array', 'ArrayCollection'], true)) {
                    $inlineModel = new Model(new Type(Type::BUILTIN_TYPE_OBJECT, false, $item->type['name']), $groups);
                    $this->describe($inlineModel, $schema);
                }
                $context->popPropertyMetadata();

                continue;
            }

            foreach ($reflections as $reflection) {
                $name = $annotationsReader->getPropertyName($reflection, $name);
            }

            $property = Util::getProperty($schema, $name);

            foreach ($reflections as $reflection) {
                $annotationsReader->updateProperty($reflection, $property, $groups);
            }

            if (Generator::UNDEFINED !== $property->type || Generator::UNDEFINED !== $property->ref) {
                $context->popPropertyMetadata();

                continue;
            }
            if (null === $item->type) {
                $key = Util::searchIndexedCollectionItem($schema->properties, 'property', $name);
                unset($schema->properties[$key]);
                $context->popPropertyMetadata();

                continue;
            }

            $this->describeItem($item->type, $property, $context);
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
    public function describeItem(array $type, OA\Schema $property, Context $context)
    {
        $nestedTypeInfo = $this->getNestedTypeInArray($type);
        if (null !== $nestedTypeInfo) {
            list($nestedType, $isHash) = $nestedTypeInfo;
            if ($isHash) {
                $property->type = 'object';
                $property->additionalProperties = Util::createChild($property, OA\Property::class);

                // this is a free form object (as nested array)
                if ('array' === $nestedType['name'] && !isset($nestedType['params'][0])) {
                    // in the case of a virtual property, set it as free object type
                    $property->additionalProperties = true;

                    return;
                }

                $this->describeItem($nestedType, $property->additionalProperties, $context);

                return;
            }

            $property->type = 'array';
            $property->items = Util::createChild($property, OA\Items::class);
            $this->describeItem($nestedType, $property->items, $context);
        } elseif ('array' === $type['name']) {
            $property->type = 'object';
            $property->additionalProperties = true;
        } elseif ('string' === $type['name']) {
            $property->type = 'string';
        } elseif (in_array($type['name'], ['bool', 'boolean'], true)) {
            $property->type = 'boolean';
        } elseif (in_array($type['name'], ['int', 'integer'], true)) {
            $property->type = 'integer';
        } elseif (in_array($type['name'], ['double', 'float'], true)) {
            $property->type = 'number';
            $property->format = $type['name'];
        } elseif (is_subclass_of($type['name'], \DateTimeInterface::class)) {
            $property->type = 'string';
            $property->format = 'date-time';
        } else {
            $groups = $this->computeGroups($context, $type);

            $model = new Model(new Type(Type::BUILTIN_TYPE_OBJECT, false, $type['name']), $groups);
            $modelRef = $this->modelRegistry->register($model);

            $customFields = (array) $property->jsonSerialize();
            unset($customFields['property']);
            if (empty($customFields)) { // no custom fields
                $property->ref = $modelRef;
            } else {
                $property->allOf = [new OA\Schema(['ref' => $modelRef])];
            }

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
