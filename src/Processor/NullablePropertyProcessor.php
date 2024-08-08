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

namespace Nelmio\ApiDocBundle\Processor;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;

/**
 * Processor to clean up the generated OpenAPI documentation for nullable properties.
 */
final class NullablePropertyProcessor
{
    public function __invoke(Analysis $analysis): void
    {
        if (Generator::isDefault($analysis->openapi->components) || Generator::isDefault($analysis->openapi->components->schemas)) {
            return;
        }

        /** @var OA\Schema[] $schemas */
        $schemas = $analysis->openapi->components->schemas;

        foreach ($schemas as $schema) {
            if (Generator::UNDEFINED === $schema->properties) {
                continue;
            }

            foreach ($schema->properties as $property) {
                if (Generator::UNDEFINED !== $property->nullable) {
                    if (!$property->nullable) {
                        // if already false mark it as undefined (so it does not show up as `nullable: false`)
                        $property->nullable = Generator::UNDEFINED; /* @phpstan-ignore-line */
                    }
                }
            }
        }
    }
}
