<?php

declare(strict_types=1);

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Processor;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber\SymfonyMapQueryStringDescriber;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * A processor that adds query parameters to operations that have a MapQueryString attribute.
 * A processor is used to ensure that a Model has been created.
 *
 * @see SymfonyMapQueryStringDescriber
 */
final class MapQueryStringProcessor
{
    public function __invoke(Analysis $analysis): void
    {
        /** @var OA\Operation[] $operations */
        $operations = $analysis->getAnnotationsOfType(OA\Operation::class);

        foreach ($operations as $operation) {
            if (!isset($operation->_context->{SymfonyMapQueryStringDescriber::CONTEXT_KEY})) {
                continue;
            }

            $mapQueryStringContexts = $operation->_context->{SymfonyMapQueryStringDescriber::CONTEXT_KEY};
            if (!\is_array($mapQueryStringContexts)) {
                throw new \LogicException(\sprintf('MapQueryString contexts not found for operation "%s"', $operation->operationId));
            }

            foreach ($mapQueryStringContexts as $mapQueryStringContext) {
                $this->addQueryParameters($analysis, $operation, $mapQueryStringContext);
            }
        }
    }

    /**
     * @param array<string, mixed> $mapQueryStringContext
     */
    private function addQueryParameters(Analysis $analysis, OA\Operation $operation, array $mapQueryStringContext): void
    {
        $argumentMetaData = $mapQueryStringContext[SymfonyMapQueryStringDescriber::CONTEXT_ARGUMENT_METADATA];
        if (!$argumentMetaData instanceof ArgumentMetadata) {
            throw new \LogicException(\sprintf('MapQueryString ArgumentMetaData not found for operation "%s"', $operation->operationId));
        }

        $modelRef = $mapQueryStringContext[SymfonyMapQueryStringDescriber::CONTEXT_MODEL_REF];
        if (!isset($modelRef)) {
            throw new \LogicException(\sprintf('MapQueryString Model reference not found for operation "%s"', $operation->operationId));
        }

        $nativeModelName = str_replace(OA\Components::SCHEMA_REF, '', $modelRef);

        $schemaModel = Util::getSchema($analysis->openapi, $nativeModelName);

        // There are no properties to map to query parameters
        if (Generator::UNDEFINED === $schemaModel->properties) {
            return;
        }

        $isModelOptional = $argumentMetaData->hasDefaultValue() || $argumentMetaData->isNullable();

        foreach ($schemaModel->properties as $property) {
            $name = 'array' === $property->type
                ? $property->property.'[]'
                : $property->property;

            $operationParameter = Util::getOperationParameter($operation, $name, 'query');

            // Remove incompatible properties
            $propertyVars = get_object_vars($property);
            unset($propertyVars['property']);

            $schema = new OA\Schema($propertyVars);

            Util::modifyAnnotationValue($operationParameter, 'schema', $schema);
            Util::modifyAnnotationValue($operationParameter, 'name', $property->property);
            Util::modifyAnnotationValue($operationParameter, 'description', $schema->description);
            Util::modifyAnnotationValue($operationParameter, 'required', $schema->required);
            Util::modifyAnnotationValue($operationParameter, 'deprecated', $schema->deprecated);
            Util::modifyAnnotationValue($operationParameter, 'example', $schema->example);

            if ($isModelOptional) {
                Util::modifyAnnotationValue($operationParameter, 'required', false);
            } elseif (\is_array($schemaModel->required) && \in_array($property->property, $schemaModel->required, true)) {
                Util::modifyAnnotationValue($operationParameter, 'required', true);
            } else {
                Util::modifyAnnotationValue($operationParameter, 'required', false);
            }
        }
    }
}
