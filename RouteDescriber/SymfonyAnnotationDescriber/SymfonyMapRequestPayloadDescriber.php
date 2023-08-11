<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\SymfonyAnnotationDescriber;

use InvalidArgumentException;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Generator;
use ReflectionParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use OpenApi\Annotations as OA;

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
        $contentSchema->ref = new \Nelmio\ApiDocBundle\Annotation\Model(type: $parameter->getType()->getName());
        $contentSchema->type = 'object';

        $schema = Util::getProperty($contentSchema, $parameter->getName());

        SymfonyAnnotationHelper::describeCommonSchemaFromParameter($schema, $parameter);
    }

    private function getContentSchemaForType(OA\RequestBody $requestBody, string $type): OA\Schema
    {
        $requestBody->content = Generator::UNDEFINED !== $requestBody->content ? $requestBody->content : [];
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
            $schema->type = 'object';
        }

        return Util::getChild(
            $requestBody->content[$contentType],
            OA\Schema::class
        );
    }
}
