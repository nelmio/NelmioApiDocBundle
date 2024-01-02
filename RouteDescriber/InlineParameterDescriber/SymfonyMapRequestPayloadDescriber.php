<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\InlineParameterDescriber;

use InvalidArgumentException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class SymfonyMapRequestPayloadDescriber implements InlineParameterDescriberInterface
{
    public function supports(ArgumentMetadata $argumentMetadata): bool
    {
        if (!$argumentMetadata->getAttributes(MapRequestPayload::class, ArgumentMetadata::IS_INSTANCEOF)) {
            return false;
        }

        return $argumentMetadata->getType() && class_exists($argumentMetadata->getType());
    }

    public function describe(OA\OpenApi $api, OA\Operation $operation, ArgumentMetadata $argumentMetadata): void
    {
        $attribute = $argumentMetadata->getAttributes(MapRequestPayload::class, ArgumentMetadata::IS_INSTANCEOF)[0];

        /** @var OA\RequestBody $requestBody */
        $requestBody = Util::getChild($operation, OA\RequestBody::class);
        Util::modifyAnnotationValue($requestBody, 'required', !($argumentMetadata->hasDefaultValue() || $argumentMetadata->isNullable()));

        $formats = $attribute->acceptFormat;
        if (!is_array($formats)) {
            $formats = [$attribute->acceptFormat ?? 'json'];
        }

        foreach ($formats as $format) {
            $contentSchema = $this->getContentSchemaForType($requestBody, $format);
            Util::modifyAnnotationValue($contentSchema, 'ref', new Model(type: $argumentMetadata->getType()));
            Util::modifyAnnotationValue($contentSchema, 'type', 'object');

            Util::getProperty($contentSchema, $argumentMetadata->getName());
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
                throw new InvalidArgumentException('Unsupported media type');
        }

        if (!isset($requestBody->content[$contentType])) {
            $weakContext = Util::createWeakContext($requestBody->_context);
            $requestBody->content[$contentType] = new OA\MediaType(
                [
                    'mediaType' => $contentType,
                    '_context' => $weakContext,
                ]
            );

            /** @var OA\Schema $schema */
            $schema = Util::getChild(
                $requestBody->content[$contentType],
                OA\Schema::class
            );
            Util::modifyAnnotationValue($schema, 'type', 'object');
        }

        return Util::getChild(
            $requestBody->content[$contentType],
            OA\Schema::class
        );
    }
}
