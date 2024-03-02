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

use Hateoas\Configuration\Metadata\ClassMetadata;
use Hateoas\Configuration\Relation;
use Hateoas\Serializer\Metadata\RelationPropertyMetadata;
use Metadata\MetadataFactoryInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;

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
    public function describe(Model $model, OA\Schema $schema): void
    {
        $this->JMSModelDescriber->describe($model, $schema);

        /**
         * @var ClassMetadata
         */
        $metadata = $this->getHateoasMetadata($model);
        if (null === $metadata) {
            return;
        }

        $schema->type = 'object';
        $context = $this->JMSModelDescriber->getSerializationContext($model);

        /** @var Relation $relation */
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
            $relationSchema = Util::getProperty($schema, $relation->getEmbedded() ? '_embedded' : '_links');
            $relationSchema->readOnly = true;

            $property = Util::getProperty($relationSchema, $relation->getName());
            if ($embedded && method_exists($embedded, 'getType') && $embedded->getType()) {
                $this->JMSModelDescriber->describeItem($embedded->getType(), $property, $context);
            } else {
                $property->type = 'object';
            }
            if ($relation->getHref()) {
                $hrefProp = Util::getProperty($property, 'href');
                $hrefProp->type = 'string';
                $this->setAttributeProperties($relation, $property);
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

    private function setAttributeProperties(Relation $relation, OA\Property $subProperty): void
    {
        foreach ($relation->getAttributes() as $attribute => $value) {
            $subSubProp = Util::getProperty($subProperty, $attribute);
            switch (gettype($value)) {
                case 'integer':
                    $subSubProp->type = 'integer';
                    $subSubProp->default = $value;

                    break;
                case 'double':
                case 'float':
                    $subSubProp->type = 'number';
                    $subSubProp->default = $value;

                    break;
                case 'boolean':
                    $subSubProp->type = 'boolean';
                    $subSubProp->default = $value;

                    break;
                case 'string':
                    $subSubProp->type = 'string';
                    $subSubProp->default = $value;

                    break;
            }
        }
    }
}
