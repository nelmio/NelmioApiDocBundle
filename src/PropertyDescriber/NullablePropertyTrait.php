<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\PropertyDescriber;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\PropertyInfo\Type;

/**
 * @deprecated Since 4.17.0, {@see NullablePropertyDescriber} instead.
 */
trait NullablePropertyTrait
{
    protected function setNullableProperty(Type $type, OA\Schema $property, ?OA\Schema $schema, array $context = []): void
    {
        trigger_deprecation(
            'nelmio/api-doc-bundle',
            '4.17.0',
            'Use %s instead',
            NullablePropertyDescriber::class,
        );

        if (Generator::UNDEFINED !== $property->nullable) {
            if (!$property->nullable) {
                // if already false mark it as undefined (so it does not show up as `nullable: false`)
                $property->nullable = Generator::UNDEFINED;
            }

            return;
        }

        if ($type->isNullable()) {
            $property->nullable = true;
        }

        if (!$type->isNullable() && Generator::UNDEFINED !== $property->default) {
            return;
        }

        if (!$type->isNullable() && null !== $schema) {
            $propertyName = Util::getSchemaPropertyName($schema, $property);
            if (null === $propertyName) {
                return;
            }

            $existingRequiredFields = Generator::UNDEFINED !== $schema->required ? $schema->required : [];
            $existingRequiredFields[] = $propertyName;

            $schema->required = array_values(array_unique($existingRequiredFields));
        }
    }
}
