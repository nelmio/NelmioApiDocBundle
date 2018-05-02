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

class BazingaHateoasModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    private $factory;

    public function __construct(MetadataFactoryInterface $factory)
    {
        $this->factory = $factory;
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
