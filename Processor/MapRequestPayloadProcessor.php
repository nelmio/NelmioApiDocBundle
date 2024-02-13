<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Processor;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber\SymfonyMapRequestPayloadDescriber;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use OpenApi\Processors\ProcessorInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * A processor that adds query parameters to operations that have a MapRequestPayload attribute.
 * A processor is used to ensure that a Model has been created.
 *
 * @see SymfonyMapRequestPayloadDescriber
 */
final class MapRequestPayloadProcessor implements ProcessorInterface
{
    public function __invoke(Analysis $analysis)
    {
        /** @var OA\Operation[] $operations */
        $operations = $analysis->getAnnotationsOfType(OA\Operation::class);

        foreach ($operations as $operation) {
            if (!isset($operation->_context->{SymfonyMapRequestPayloadDescriber::CONTEXT_ARGUMENT_METADATA})) {
                continue;
            }

            $argumentMetaData = $operation->_context->{SymfonyMapRequestPayloadDescriber::CONTEXT_ARGUMENT_METADATA};
            if (!$argumentMetaData instanceof ArgumentMetadata) {
                throw new \LogicException(sprintf('MapRequestPayload ArgumentMetaData not found for operation "%s"', $operation->operationId));
            }

            /** @var MapRequestPayload $attribute */
            if (!$attribute = $argumentMetaData->getAttributes(MapRequestPayload::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null) {
                throw new \LogicException(sprintf('Operation "%s" does not contain attribute of "%s', $operation->operationId, MapRequestPayload::class));
            }

            $modelRef = $operation->_context->{SymfonyMapRequestPayloadDescriber::CONTEXT_MODEL_REF};
            if (!isset($modelRef)) {
                throw new \LogicException(sprintf('MapRequestPayload Model reference not found for operation "%s"', $operation->operationId));
            }

            /** @var OA\RequestBody $requestBody */
            $requestBody = Util::getChild($operation, OA\RequestBody::class);
            Util::modifyAnnotationValue($requestBody, 'required', !($argumentMetaData->hasDefaultValue() || $argumentMetaData->isNullable()));

            $formats = $attribute->acceptFormat;
            if (!is_array($formats)) {
                $formats = [$attribute->acceptFormat ?? 'json'];
            }

            foreach ($formats as $format) {
                if (!Generator::isDefault($requestBody->content)) {
                    continue;
                }

                $contentSchema = $this->getContentSchemaForType($requestBody, $format);

                Util::modifyAnnotationValue($contentSchema, 'ref', $modelRef);

                if ($argumentMetaData->isNullable()) {
                    $contentSchema->nullable = true;
                }
            }
        }
    }

    private function getContentSchemaForType(OA\RequestBody $requestBody, string $type): OA\Schema
    {
        Util::modifyAnnotationValue($requestBody, 'content', []);
        switch ($type) {
            case 'json':
                $contentType = 'application/json';

                break;
            case 'xml':
                $contentType = 'application/xml';

                break;
            default:
                throw new \InvalidArgumentException('Unsupported media type');
        }

        if (!isset($requestBody->content[$contentType])) {
            $weakContext = Util::createWeakContext($requestBody->_context);
            $requestBody->content[$contentType] = new OA\MediaType(
                [
                    'mediaType' => $contentType,
                    '_context' => $weakContext,
                ]
            );
        }

        return Util::getChild(
            $requestBody->content[$contentType],
            OA\Schema::class
        );
    }
}
