<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\PropertyInfo\Type;

final class SymfonyMapRequestPayloadDescriber implements RouteArgumentDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    public function describe(ArgumentMetadata $argumentMetadata, OA\Operation $operation): void
    {
        /** @var MapRequestPayload $attribute */
        if (!$attribute = $argumentMetadata->getAttributes(MapRequestPayload::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null) {
            return;
        }

        $model = $this->modelRegistry->register(new Model(
            new Type(Type::BUILTIN_TYPE_OBJECT, false, $argumentMetadata->getType()),
            serializationContext: $attribute->serializationContext,
        ));

        /** @var OA\RequestBody $requestBody */
        $requestBody = Util::getChild($operation, OA\RequestBody::class);
        Util::modifyAnnotationValue($requestBody, 'required', !($argumentMetadata->hasDefaultValue() || $argumentMetadata->isNullable()));

        $formats = $attribute->acceptFormat;
        if (!is_array($formats)) {
            $formats = [$attribute->acceptFormat ?? 'json'];
        }

        foreach ($formats as $format) {
            $contentSchema = $this->getContentSchemaForType($requestBody, $format);
            Util::modifyAnnotationValue($contentSchema, 'ref', $model);

            if ($argumentMetadata->isNullable()) {
                $contentSchema->nullable = true;
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
