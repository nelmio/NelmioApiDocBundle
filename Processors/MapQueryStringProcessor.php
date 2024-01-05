<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Processors;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\RouteDescriber\InlineParameterDescriber\SymfonyMapQueryStringDescriber;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use OpenApi\Processors\ProcessorInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * A processor that adds query parameters to operations that have a MapQueryString attribute.
 * A processor is used to ensure that a Model is created.
 *
 * @see \Nelmio\ApiDocBundle\RouteDescriber\InlineParameterDescriber\SymfonyMapQueryStringDescriber
 */
final class MapQueryStringProcessor implements ProcessorInterface
{
    public function __invoke(Analysis $analysis)
    {
        /** @var OA\Operation[] $operations */
        $operations = $analysis->getAnnotationsOfType(OA\Operation::class);

        foreach ($operations as $operation) {
            if (!isset($operation->_context->{SymfonyMapQueryStringDescriber::CONTEXT_ARGUMENT_METADATA})) {
                continue;
            }

            $argumentMetaData = $operation->_context->{SymfonyMapQueryStringDescriber::CONTEXT_ARGUMENT_METADATA};
            if (!$argumentMetaData instanceof ArgumentMetadata) {
                continue;
            }

            $modelRef = $operation->_context->{SymfonyMapQueryStringDescriber::CONTEXT_MODEL_REF};
            if (!isset($modelRef)) {
                throw new \LogicException(sprintf('MapQueryString Model reference not found for operation "%s"', $operation->operationId));
            }

            $nativeModelName = str_replace(OA\Components::SCHEMA_REF, '', $modelRef);

            $schemaModel = Util::getSchema($analysis->openapi, $nativeModelName);

            // There are no properties to map to query parameters
            if (Generator::UNDEFINED === $schemaModel->properties) {
                return;
            }

            $isModelOptional = $argumentMetaData->hasDefaultValue() || $argumentMetaData->isNullable();

            foreach ($schemaModel->properties as $property) {
                $operationParameter = Util::getOperationParameter($operation, $property->property, 'query');
                Util::modifyAnnotationValue($operationParameter, 'schema', $property);
                Util::modifyAnnotationValue($operationParameter, 'name', $property->property);
                Util::modifyAnnotationValue($operationParameter, 'description', $property->description);
                Util::modifyAnnotationValue($operationParameter, 'required', $property->required);
                Util::modifyAnnotationValue($operationParameter, 'deprecated', $property->deprecated);
                Util::modifyAnnotationValue($operationParameter, 'example', $property->example);

                if ($isModelOptional) {
                    Util::modifyAnnotationValue($operationParameter, 'required', false);
                } elseif (is_array($schemaModel->required) && in_array($property->property, $schemaModel->required, true)) {
                    Util::modifyAnnotationValue($operationParameter, 'required', true);
                } else {
                    Util::modifyAnnotationValue($operationParameter, 'required', false);
                }
            }
        }
    }
}
