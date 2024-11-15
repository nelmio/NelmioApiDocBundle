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

    private MetadataFactoryInterface $factory;
    private JMSModelDescriber $JMSModelDescriber;

    public function __construct(MetadataFactoryInterface $factory, JMSModelDescriber $JMSModelDescriber)
    {
        $this->factory = $factory;
        $this->JMSModelDescriber = $JMSModelDescriber;
    }

    public function setModelRegistry(ModelRegistry $modelRegistry): void
    {
        $this->modelRegistry = $modelRegistry;
        $this->JMSModelDescriber->setModelRegistry($modelRegistry);
    }

    public function describe(Model $model, OA\Schema $schema): void
    {
        $this->JMSModelDescriber->describe($model, $schema);

        $metadata = $this->getHateoasMetadata($model);
        if (!$metadata instanceof ClassMetadata) {
            return;
        }

        $schema->type = 'object';
        $context = $this->JMSModelDescriber->getSerializationContext($model);

        foreach ($metadata->getRelations() as $relation) {
            if (null === $relation->getEmbedded() && null === $relation->getHref()) {
                continue;
            }
            $item = new RelationPropertyMetadata($relation->getExclusion(), $relation);

            if (null !== $context->getExclusionStrategy() && $context->getExclusionStrategy()->shouldSkipProperty($item, $context)) {
                continue;
            }

            $context->pushPropertyMetadata($item);

            $embedded = $relation->getEmbedded();
            $relationSchema = Util::getProperty($schema, null !== $relation->getEmbedded() ? '_embedded' : '_links');
            $relationSchema->readOnly = true;

            $property = Util::getProperty($relationSchema, $relation->getName());
            if (null !== $embedded && method_exists($embedded, 'getType') && null !== $embedded->getType()) {
                $this->JMSModelDescriber->describeItem($embedded->getType(), $property, $context, $model->getSerializationContext());
            } else {
                $property->type = 'object';
            }
            if (null !== $relation->getHref()) {
                $hrefProp = Util::getProperty($property, 'href');
                $hrefProp->type = 'string';
                $this->setAttributeProperties($relation, $property);
            }

            $context->popPropertyMetadata();
        }
    }

    private function getHateoasMetadata(Model $model): ?object
    {
        try {
            return $this->factory->getMetadataForClass($model->getType()->getClassName());
        } catch (\ReflectionException $e) {
        }

        return null;
    }

    public function supports(Model $model): bool
    {
        return $this->JMSModelDescriber->supports($model)
            || $this->getHateoasMetadata($model) instanceof ClassMetadata;
    }

    private function setAttributeProperties(Relation $relation, OA\Property $subProperty): void
    {
        foreach ($relation->getAttributes() as $attribute => $value) {
            $subSubProp = Util::getProperty($subProperty, $attribute);
            switch (\gettype($value)) {
                case 'integer':
                    $subSubProp->type = 'integer';
                    $subSubProp->default = $value;

                    break;
                case 'double':
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
