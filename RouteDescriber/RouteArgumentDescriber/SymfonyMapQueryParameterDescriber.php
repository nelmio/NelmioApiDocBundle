<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use OpenApi\Processors\Concerns\TypesTrait;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class SymfonyMapQueryParameterDescriber implements RouteArgumentDescriberInterface
{
    use TypesTrait;

    public function describe(ArgumentMetadata $argumentMetadata, OA\Operation $operation): void
    {
        /** @var MapQueryParameter $attribute */
        if (!$attribute = $argumentMetadata->getAttributes(MapQueryParameter::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null) {
            return;
        }

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

        if (Generator::UNDEFINED === $schema->type) {
            $this->mapNativeType($schema, $argumentMetadata->getType());
        }

        if (Generator::UNDEFINED === $schema->nullable && $argumentMetadata->isNullable()) {
            Util::modifyAnnotationValue($schema, 'nullable', true);
        }
    }
}
