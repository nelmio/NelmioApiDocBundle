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
use JMS\Serializer\Metadata\PropertyMetadata;
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

            if ($type = $this->getNestedTypeInArray($item)) {
                $property->setType('array');
                $property = $property->getItems();
            } else {
                $type = $item->type['name'];
            }

            if (in_array($type, ['boolean', 'string', 'array'])) {
                $property->setType($type);
            } elseif (in_array($type, ['int', 'integer'])) {
                $property->setType('integer');
            } elseif (in_array($type, ['double', 'float'])) {
                $property->setType('number');
                $property->setFormat($type);
            } elseif (in_array($type, ['DateTime', 'DateTimeImmutable'])) {
                $property->setType('string');
                $property->setFormat('date-time');
            } else {
                // we can use property type also for custom handlers, then we don't have here real class name
                if (!class_exists($type)) {
                    continue;
                }

                $property->setRef(
                    $this->modelRegistry->register(new Model(new Type(Type::BUILTIN_TYPE_OBJECT, false, $type), $groups))
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
