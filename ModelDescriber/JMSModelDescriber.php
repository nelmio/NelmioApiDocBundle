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
            if (isset($groups[$name]) && is_array($groups[$name])) {
                $groups = $model->getGroups()[$name];
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

            $this->describeItem($item->type, $property, $groups);
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

    /**
     * @param string     $type
     * @param array|null $groups
     *
     * @return array|null
     */
    private function findPropertyType(string $type, array $groups = null)
    {
        $typeDef = [];
        if (in_array($type, ['boolean', 'string', 'array'])) {
            $typeDef['type'] = $type;
        } elseif (in_array($type, ['int', 'integer'])) {
            $typeDef['type'] = 'integer';
        } elseif (in_array($type, ['double', 'float'])) {
            $typeDef['type'] = 'number';
            $typeDef['format'] = $type;
        } elseif (is_subclass_of($type, \DateTimeInterface::class)) {
            $typeDef['type'] = 'string';
            $typeDef['format'] = 'date-time';
        } else {
            // we can use property type also for custom handlers, then we don't have here real class name
            if (!class_exists($type)) {
                return null;
            }

            $typeDef['$ref'] = $this->modelRegistry->register(
                new Model(new Type(Type::BUILTIN_TYPE_OBJECT, false, $type), $groups)
            );
        }

        return $typeDef;
    }

    private function registerPropertyType(array $typeDef, $property)
    {
        if (isset($typeDef['$ref'])) {
            $property->setRef($typeDef['$ref']);
        } else {
            if (isset($typeDef['type'])) {
                $property->setType($typeDef['type']);
            }
            if (isset($typeDef['format'])) {
                $property->setFormat($typeDef['format']);
            }
        }
    }

    private function describeItem(array $type, Schema $property, array $groups = null)
    {
        if (list($nestedType, $isHash) = $this->getNestedTypeInArray($type)) { // @ todo update a bit getNestedTypeInArray and describe ($type = $item->type)
            if ($isHash) {
                $property->setType('object');
                // in the case of a virtual property, set it as free object type
                $property->merge(['additionalProperties' => []]);

                // this is a free form object (as nested array)
                if ('array' === $nestedType['name'] && !isset($nestedType['params'][0])) {
                    return;
                }

                $this->describeItem($nestedType, $property->getAdditionalProperties(), $groups);
                return;
            }

            $property->setType('array');
            $this->describeItem($nestedType, $property->getItems(), $groups);
        }

        if ($typeDef = $this->findPropertyType($type['name'], $groups)) {
            $this->registerPropertyType($typeDef, $property);
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
}
