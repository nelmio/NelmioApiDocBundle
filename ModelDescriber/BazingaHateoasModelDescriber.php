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
use Hateoas\Configuration\Relation;
use Hateoas\Serializer\Metadata\RelationPropertyMetadata;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\SerializationContext;
use Metadata\MetadataFactoryInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Model\ModelRegistry;

class BazingaHateoasModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    private $factory;
    private $JMSModelDescriber;

    public function __construct(MetadataFactoryInterface $factory, JMSModelDescriber $JMSModelDescriber)
    {
        $this->factory = $factory;
        $this->JMSModelDescriber = $JMSModelDescriber;
    }

    public function setModelRegistry(ModelRegistry $modelRegistry)
    {
        $this->modelRegistry = $modelRegistry;
        $this->JMSModelDescriber->setModelRegistry($modelRegistry);
    }

    /**
     * {@inheritdoc}
     */
    public function describe(Model $model, Schema $schema)
    {
        $this->JMSModelDescriber->describe($model, $schema);

        $metadata = $this->getHateoasMetadata($model);
        if (null === $metadata) {
            return;
        }

        $groupsExclusion = null !== $model->getGroups() ? new GroupsExclusionStrategy($model->getGroups()) : null;

        $schema->setType('object');

        foreach ($metadata->getRelations() as $relation) {
            if (!$relation->getEmbedded() && !$relation->getHref()) {
                continue;
            }

            if (null !== $groupsExclusion && $relation->getExclusion()) {
                $item = new RelationPropertyMetadata($relation->getExclusion(), $relation);

                // filter groups
                if ($groupsExclusion->shouldSkipProperty($item, SerializationContext::create())) {
                    continue;
                }
            }

            $name = $relation->getName();

            $relationSchema = $schema->getProperties()->get($relation->getEmbedded() ? '_embedded' : '_links');

            $properties = $relationSchema->getProperties();
            $relationSchema->setReadOnly(true);

            $property = $properties->get($name);
            $property->setType('object');

            if ($relation->getHref()) {
                $subProperties = $property->getProperties();

                $hrefProp = $subProperties->get('href');
                $hrefProp->setType('string');

                $this->setAttributeProperties($relation, $subProperties);
            }
        }
    }

    private function getHateoasMetadata(Model $model)
    {
        $className = $model->getType()->getClassName();

        try {
            if ($metadata = $this->factory->getMetadataForClass($className)) {
                return $metadata;
            }
        } catch (\ReflectionException $e) {
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $model): bool
    {
        return $this->JMSModelDescriber->supports($model) || null !== $this->getHateoasMetadata($model);
    }

    private function setAttributeProperties(Relation $relation, $subProperties)
    {
        foreach ($relation->getAttributes() as $attribute => $value) {
            $subSubProp = $subProperties->get($attribute);
            switch (gettype($value)) {
                case 'integer':
                    $subSubProp->setType('integer');
                    $subSubProp->setDefault($value);

                    break;
                case 'double':
                case 'float':
                    $subSubProp->setType('number');
                    $subSubProp->setDefault($value);

                    break;
                case 'boolean':
                    $subSubProp->setType('boolean');
                    $subSubProp->setDefault($value);

                    break;
                case 'string':
                    $subSubProp->setType('string');
                    $subSubProp->setDefault($value);

                    break;
            }
        }
    }
}
