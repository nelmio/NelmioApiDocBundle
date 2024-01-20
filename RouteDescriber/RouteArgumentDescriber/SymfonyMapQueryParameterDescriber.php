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

        if ('array' === $schema->type) {
            $this->augmentArrayType($schema, $attribute);
        } else {
            $properties = $this->describeValidateFilter($attribute->filter, $attribute->flags, $attribute->options);
        }
    }

    private function augmentArrayType(OA\Schema $schema, MapQueryParameter $attribute): void
    {
        $properties = $this->describeValidateFilter($attribute->filter, $attribute->flags, $attribute->options);

        Util::getChild($schema, OA\Items::class, $properties);
    }

    /**
     * @see https://www.php.net/manual/en/filter.filters.validate.php
     */
    private function describeValidateFilter(?int $filter, int $flags, array $options): array
    {
        if ($filter & FILTER_VALIDATE_BOOLEAN) {
            return ['type' => 'boolean'];
        }

        if ($filter & FILTER_VALIDATE_DOMAIN) {
            return ['type' => 'string', 'format' => 'hostname'];
        }

        if ($filter & FILTER_VALIDATE_EMAIL) {
            return ['type' => 'string', 'format' => 'email'];
        }

        if ($filter & FILTER_VALIDATE_FLOAT) {
            return ['type' => 'number', 'format' => 'float'];
        }

        if ($filter & FILTER_VALIDATE_INT) {
            if ($options['min_range'] ?? false) {
                $props = ['minimum' => $options['min_range']];
            }

            if ($options['max_range'] ?? false) {
                $props = ['maximum' => $options['max_range']];
            }

            return ['type' => 'integer', ...$props ?? []];
        }

        if ($filter & FILTER_VALIDATE_IP) {
            $format = match ($flags) {
                FILTER_FLAG_IPV4 => 'ipv4',
                FILTER_FLAG_IPV6 => 'ipv6',
                default => 'ip',
            };

            return ['type' => 'string', 'format' => $format];
        }

        if ($filter & FILTER_VALIDATE_MAC) {
            return ['type' => 'string', 'format' => 'mac'];
        }

        if ($filter & FILTER_VALIDATE_REGEXP) {
            return ['type' => 'string', 'pattern' => $options['regexp']];
        }

        if ($filter & FILTER_VALIDATE_URL) {
            return ['type' => 'string', 'format' => 'uri'];
        }

        return [];
    }
}
