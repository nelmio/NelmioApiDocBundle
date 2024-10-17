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

use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type;

/**
 * Contains helper methods that add `discriminator` and `oneOf` values to
 * Open API schemas to support poly morphism.
 *
 * @see https://swagger.io/docs/specification/data-models/inheritance-and-polymorphism/
 *
 * @internal
 */
trait ApplyOpenApiDiscriminatorTrait
{
    /**
     * @param Model                 $model                 the model that's being described, This is used to pass groups and config
     *                                                     down to the children models in `oneOf`
     * @param OA\Schema             $schema                The Open API schema to which `oneOf` and `discriminator` properties
     *                                                     will be added
     * @param string                $discriminatorProperty The property that determine which model will be unsierailized
     * @param array<string, string> $typeMap               the map of $discriminatorProperty values to their
     *                                                     types
     */
    protected function applyOpenApiDiscriminator(
        Model $model,
        OA\Schema $schema,
        ModelRegistry $modelRegistry,
        string $discriminatorProperty,
        array $typeMap
    ): void {
        $weakContext = Util::createWeakContext($schema->_context);

        $schema->oneOf = [];
        $schema->discriminator = new OA\Discriminator(['_context' => $weakContext]);
        $schema->discriminator->propertyName = $discriminatorProperty;
        $schema->discriminator->mapping = [];
        foreach ($typeMap as $propertyValue => $className) {
            $oneOfSchema = new OA\Schema(['_context' => $weakContext]);
            $oneOfSchema->ref = $modelRegistry->register(new Model(
                new Type(Type::BUILTIN_TYPE_OBJECT, false, $className),
                $model->getGroups(),
                $model->getOptions(),
                $model->getSerializationContext()
            ));
            $schema->oneOf[] = $oneOfSchema;
            $schema->discriminator->mapping[$propertyValue] = $oneOfSchema->ref;
        }
    }
}
