<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\SymfonyAnnotationDescriber;

use InvalidArgumentException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use ReflectionParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

final class SymfonyMapRequestPayloadDescriber implements SymfonyAnnotationDescriber
{
    public function supports(ReflectionParameter $parameter): bool
    {
        if (!SymfonyAnnotationHelper::getAttribute($parameter, MapRequestPayload::class)) {
            return false;
        }

        return $parameter->hasType();
    }

    public function describe(OA\OpenApi $api, OA\Operation $operation, ReflectionParameter $parameter): void
    {
        $attribute = SymfonyAnnotationHelper::getAttribute($parameter, MapRequestPayload::class);

        /** @var OA\RequestBody $requestBody */
        $requestBody = Util::getChild($operation, OA\RequestBody::class);

        if (!is_array($attribute->acceptFormat)) {
            $this->describeRequestBody($requestBody, $parameter, $attribute->acceptFormat ?? 'json');
        } else {
            foreach ($attribute->acceptFormat as $format) {
                $this->describeRequestBody($requestBody, $parameter, $format);
            }
        }
    }

    private function describeRequestBody(OA\RequestBody $requestBody, ReflectionParameter $parameter, string $format): void
    {
        $contentSchema = $this->getContentSchemaForType($requestBody, $format);
        SymfonyAnnotationHelper::modifyAnnotationValue($contentSchema, 'ref', new Model(type: $parameter->getType()->getName()));
        SymfonyAnnotationHelper::modifyAnnotationValue($contentSchema, 'type', 'object');

        $schema = Util::getProperty($contentSchema, $parameter->getName());

        SymfonyAnnotationHelper::describeCommonSchemaFromParameter($schema, $parameter);
    }

    private function getContentSchemaForType(OA\RequestBody $requestBody, string $type): OA\Schema
    {
        SymfonyAnnotationHelper::modifyAnnotationValue($requestBody, 'content', []);
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
            SymfonyAnnotationHelper::modifyAnnotationValue($schema, 'type', 'object');
        }

        return Util::getChild(
            $requestBody->content[$contentType],
            OA\Schema::class
        );
    }
}
