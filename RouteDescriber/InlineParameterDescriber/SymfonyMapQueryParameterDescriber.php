<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\InlineParameterDescriber;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Processors\Concerns\TypesTrait;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class SymfonyMapQueryParameterDescriber implements InlineParameterDescriberInterface
{
    use TypesTrait;

    public function supports(ArgumentMetadata $argumentMetadata): bool
    {
        if (!class_exists(MapQueryParameter::class)) {
            return false;
        }

        if (!$argumentMetadata->getAttributes(MapQueryParameter::class, ArgumentMetadata::IS_INSTANCEOF)) {
            return false;
        }

        return null !== $argumentMetadata->getType();
    }

    public function describe(OA\OpenApi $api, OA\Operation $operation, ArgumentMetadata $argumentMetadata): void
    {
        $attribute = $argumentMetadata->getAttributes(MapQueryParameter::class, ArgumentMetadata::IS_INSTANCEOF)[0];

        $operationParameter = Util::getOperationParameter($operation, $attribute->name ?? $argumentMetadata->getName(), 'query');

        Util::modifyAnnotationValue($operationParameter, 'required', !($argumentMetadata->hasDefaultValue() || $argumentMetadata->isNullable()));

        /** @var OA\Schema $schema */
        $schema = Util::getChild($operationParameter, OA\Schema::class);

        if (FILTER_VALIDATE_REGEXP === $attribute->filter) {
            Util::modifyAnnotationValue($schema, 'pattern', $attribute->options['regexp']);
        }

        if ($argumentMetadata->hasDefaultValue()) {
            Util::modifyAnnotationValue($schema, 'default', $argumentMetadata->getDefaultValue());
        }

        $this->mapNativeType($schema, $argumentMetadata->getType());
    }
}
