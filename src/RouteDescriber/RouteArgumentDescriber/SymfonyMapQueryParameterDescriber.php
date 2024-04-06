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
        if (!$attribute = $argumentMetadata->getAttributes(MapQueryParameter::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null) {
            return;
        }

        $name = $attribute->name ?? $argumentMetadata->getName();
        $name = 'array' === $argumentMetadata->getType()
            ? $name.'[]'
            : $name;

        $operationParameter = Util::getOperationParameter($operation, $name, 'query');

        Util::modifyAnnotationValue($operationParameter, 'required', !($argumentMetadata->hasDefaultValue() || $argumentMetadata->isNullable()));

        /** @var OA\Schema $schema */
        $schema = Util::getChild($operationParameter, OA\Schema::class);

        if ($argumentMetadata->hasDefaultValue()) {
            Util::modifyAnnotationValue($schema, 'default', $argumentMetadata->getDefaultValue());
        }

        if (Generator::UNDEFINED === $schema->nullable && $argumentMetadata->isNullable()) {
            Util::modifyAnnotationValue($schema, 'nullable', true);
        }

        $defaultFilter = match ($argumentMetadata->getType()) {
            'array' => null,
            'string' => \FILTER_DEFAULT,
            'int' => \FILTER_VALIDATE_INT,
            'float' => \FILTER_VALIDATE_FLOAT,
            'bool' => \FILTER_VALIDATE_BOOL,
            default => null,
        };

        $properties = $this->describeValidateFilter($attribute->filter ?? $defaultFilter, $attribute->flags, $attribute->options);

        if ('array' === $argumentMetadata->getType()) {
            $schema->type = 'array';
            Util::getChild($schema, OA\Items::class, $properties);
        } else {
            foreach ($properties as $key => $value) {
                Util::modifyAnnotationValue($schema, $key, $value);
            }
        }
    }

    /**
     * @param mixed[] $options
     *
     * @return array<string, mixed>
     *
     * @see https://www.php.net/manual/en/filter.filters.validate.php
     */
    private function describeValidateFilter(?int $filter, int $flags, array $options): array
    {
        if (null === $filter) {
            return [];
        }

        if (FILTER_VALIDATE_BOOLEAN === $filter) {
            return ['type' => 'boolean'];
        }

        if (FILTER_VALIDATE_DOMAIN === $filter) {
            return ['type' => 'string', 'format' => 'hostname'];
        }

        if (FILTER_VALIDATE_EMAIL === $filter) {
            return ['type' => 'string', 'format' => 'email'];
        }

        if (FILTER_VALIDATE_FLOAT === $filter) {
            return ['type' => 'number', 'format' => 'float'];
        }

        if (FILTER_VALIDATE_INT === $filter) {
            $props = [];
            if (array_key_exists('min_range', $options)) {
                $props['minimum'] = $options['min_range'];
            }

            if (array_key_exists('max_range', $options)) {
                $props['maximum'] = $options['max_range'];
            }

            return ['type' => 'integer', ...$props];
        }

        if (FILTER_VALIDATE_IP === $filter) {
            $format = match ($flags) {
                FILTER_FLAG_IPV4 => 'ipv4',
                FILTER_FLAG_IPV6 => 'ipv6',
                default => 'ip',
            };

            return ['type' => 'string', 'format' => $format];
        }

        if (FILTER_VALIDATE_MAC === $filter) {
            return ['type' => 'string', 'format' => 'mac'];
        }

        if (FILTER_VALIDATE_REGEXP === $filter) {
            return ['type' => 'string', 'pattern' => $options['regexp']];
        }

        if (FILTER_VALIDATE_URL === $filter) {
            return ['type' => 'string', 'format' => 'uri'];
        }

        if (FILTER_DEFAULT === $filter) {
            return ['type' => 'string'];
        }

        return [];
    }
}
