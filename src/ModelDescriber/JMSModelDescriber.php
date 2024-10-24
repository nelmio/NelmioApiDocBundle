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
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\Metadata\ClassMetadata;
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
    use ApplyOpenApiDiscriminatorTrait;

    private MetadataFactoryInterface $factory;

    private ?SerializationContextFactoryInterface $contextFactory;

    private ?PropertyNamingStrategyInterface $namingStrategy;

    private ?Reader $doctrineReader;

    /**
     * @var array<string, Context>
     */
    private array $contexts = [];

    /**
     * @var array<string, \SplStack>
     */
    private array $metadataStacks = [];

    /**
     * @var string[]
     */
    private array $mediaTypes;

    /**
     * @var array<string, bool|null>
     */
    private array $propertyTypeUseGroupsCache = [];

    private bool $useValidationGroups;

    /**
     * @param string[] $mediaTypes
     */
    public function __construct(
        MetadataFactoryInterface $factory,
        ?Reader $reader,
        array $mediaTypes,
        ?PropertyNamingStrategyInterface $namingStrategy = null,
        bool $useValidationGroups = false,
        ?SerializationContextFactoryInterface $contextFactory = null
    ) {
        $this->factory = $factory;
        $this->namingStrategy = $namingStrategy;
        $this->doctrineReader = $reader;
        $this->mediaTypes = $mediaTypes;
        $this->useValidationGroups = $useValidationGroups;
        $this->contextFactory = $contextFactory;
    }

    /**
     * @return void
     */
    public function describe(Model $model, OA\Schema $schema)
    {
        $className = $model->getType()->getClassName();
        $metadata = $this->factory->getMetadataForClass($className);
        if (!$metadata instanceof ClassMetadata) {
            throw new \InvalidArgumentException(sprintf('No metadata found for class %s.', $className));
        }

        if (null !== $metadata->discriminatorFieldName
            && $className === $metadata->discriminatorBaseClass
            && [] !== $metadata->discriminatorMap
            && Generator::UNDEFINED === $schema->discriminator) {
            $this->applyOpenApiDiscriminator(
                $model,
                $schema,
                $this->modelRegistry,
                $metadata->discriminatorFieldName,
                $metadata->discriminatorMap
            );

            return;
        }

        $annotationsReader = new AnnotationsReader(
            $this->doctrineReader,
            $this->modelRegistry,
            $this->mediaTypes,
            $this->useValidationGroups
        );
        $classResult = $annotationsReader->updateDefinition(new \ReflectionClass($className), $schema);

        if (!$classResult) {
            return;
        }
        $schema->type = 'object';

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

            if (Generator::UNDEFINED === $property->default && $item->hasDefault) {
                $property->default = $item->defaultValue;
            }

            if (null === $item->type) {
                $key = Util::searchIndexedCollectionItem($schema->properties, 'property', $name);
                unset($schema->properties[$key]);
                $context->popPropertyMetadata();

                continue;
            }

            $this->describeItem($item->type, $property, $context, $model->getSerializationContext());
            $context->popPropertyMetadata();
        }
        $context->popClassMetadata();
    }

    /**
     * @internal
     */
    public function getSerializationContext(Model $model): Context
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
            $context = null !== $this->contextFactory
                ? $this->contextFactory->createSerializationContext()
                : SerializationContext::create();

            if (null !== $model->getGroups()) {
                $context->addExclusionStrategy(new GroupsExclusionStrategy($model->getGroups()));
            }
        }

        return $context;
    }

    /**
     * @param mixed[]|null $type
     *
     * @return string[]|null
     */
    private function computeGroups(Context $context, ?array $type = null): ?array
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

    public function supports(Model $model): bool
    {
        if (($model->getSerializationContext()['useJms'] ?? null) === false) {
            return false;
        }

        $className = $model->getType()->getClassName();

        try {
            if (null !== $this->factory->getMetadataForClass($className)) {
                return true;
            }
        } catch (\ReflectionException $e) {
        }

        return false;
    }

    /**
     * @internal
     *
     * @param mixed[] $type
     * @param mixed[] $serializationContext
     */
    public function describeItem(array $type, OA\Schema $property, Context $context, array $serializationContext): void
    {
        $nestedTypeInfo = $this->getNestedTypeInArray($type);
        if (null !== $nestedTypeInfo) {
            [$nestedType, $isHash] = $nestedTypeInfo;
            if ($isHash) {
                $property->type = 'object';
                $property->additionalProperties = Util::createChild($property, OA\AdditionalProperties::class);

                // this is a free form object (as nested array)
                if ('array' === $nestedType['name'] && !isset($nestedType['params'][0])) {
                    // in the case of a virtual property, set it as free object type
                    $property->additionalProperties = true;

                    return;
                }

                $this->describeItem($nestedType, $property->additionalProperties, $context, $serializationContext);

                return;
            }

            $property->type = 'array';
            $property->items = Util::createChild($property, OA\Items::class);
            $this->describeItem($nestedType, $property->items, $context, $serializationContext);
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
        } elseif (is_a($type['name'], \DateTimeInterface::class, true)) {
            $property->type = 'string';
            $property->format = 'date-time';
        } else {
            // See https://github.com/schmittjoh/serializer/blob/5a5a03a/src/Metadata/Driver/EnumPropertiesDriver.php#L51
            if ('enum' === $type['name']
                && isset($type['params'][0])
                && function_exists('enum_exists')
            ) {
                $typeParam = $type['params'][0];
                if (isset($typeParam['name'])) {
                    $typeParam = $typeParam['name'];
                }
                if (is_string($typeParam) && enum_exists($typeParam)) {
                    $type['name'] = $typeParam;
                }

                if (isset($type['params'][1])) {
                    if ('value' !== $type['params'][1] && is_a($type['name'], \BackedEnum::class, true)) {
                        // In case of a backed enum, it is possible to serialize it using its names instead of values
                        // Set a specific serialization context property to enforce a new model, as options cannot be used to force a new model
                        // See https://github.com/schmittjoh/serializer/blob/5a5a03a71a28a480189c5a0ca95893c19f1d120c/src/Handler/EnumHandler.php#L47
                        $serializationContext[EnumModelDescriber::FORCE_NAMES] = true;
                    }
                }
            }

            $groups = $this->computeGroups($context, $type);
            unset($serializationContext['groups']);

            $model = new Model(new Type(Type::BUILTIN_TYPE_OBJECT, false, $type['name']), $groups, null, $serializationContext);
            $modelRef = $this->modelRegistry->register($model);

            $customFields = (array) $property->jsonSerialize();
            unset($customFields['property']);
            if ([] === $customFields) { // no custom fields
                $property->ref = $modelRef;
            } else {
                $weakContext = Util::createWeakContext($property->_context);
                $property->oneOf = [new OA\Schema(['ref' => $modelRef, '_context' => $weakContext])];
            }

            $this->contexts[$model->getHash()] = $context;
            $this->metadataStacks[$model->getHash()] = clone $context->getMetadataStack();
        }
    }

    /**
     * @param mixed[] $type
     *
     * @return array{0: mixed, 1: bool}|null
     */
    private function getNestedTypeInArray(array $type): ?array
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
     * @param mixed[] $type
     */
    private function propertyTypeUsesGroups(array $type): ?bool
    {
        if (array_key_exists($type['name'], $this->propertyTypeUseGroupsCache)) {
            return $this->propertyTypeUseGroupsCache[$type['name']];
        }

        try {
            $metadata = $this->factory->getMetadataForClass($type['name']);

            foreach ($metadata->propertyMetadata as $item) {
                if (isset($item->groups) && $item->groups != [GroupsExclusionStrategy::DEFAULT_GROUP]) {
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
