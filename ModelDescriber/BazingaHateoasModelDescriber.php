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
use Hateoas\Configuration\Metadata\ClassMetadata;
use Hateoas\Configuration\Relation;
use Hateoas\Serializer\Metadata\RelationPropertyMetadata;
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

        /**
         * @var ClassMetadata
         */
        $metadata = $this->getHateoasMetadata($model);
        if (null === $metadata) {
            return;
        }

        $schema->setType('object');
        $context = $this->JMSModelDescriber->getSerializationContext($model);

        foreach ($metadata->getRelations() as $relation) {
            if (!$relation->getEmbedded() && !$relation->getHref()) {
                continue;
            }
            $item = new RelationPropertyMetadata($relation->getExclusion(), $relation);

            if (null !== $context->getExclusionStrategy() && $context->getExclusionStrategy()->shouldSkipProperty($item, $context)) {
                continue;
            }

            $context->pushPropertyMetadata($item);

            $embedded = $relation->getEmbedded();
            $relationSchema = $schema->getProperties()->get($embedded ? '_embedded' : '_links');

            $properties = $relationSchema->getProperties();
            $relationSchema->setReadOnly(true);

            $name = $relation->getName();
            $property = $properties->get($name);

            if ($embedded && method_exists($embedded, 'getType') && $embedded->getType()) {
                $this->JMSModelDescriber->describeItem($embedded->getType(), $property, $context);
            } else {
                $property->setType('object');
            }
            if ($relation->getHref()) {
                $subProperties = $property->getProperties();

                $hrefProp = $subProperties->get('href');
                $hrefProp->setType('string');

                $this->setAttributeProperties($relation, $subProperties);
            }

            $context->popPropertyMetadata();
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
