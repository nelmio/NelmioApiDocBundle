<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Processor;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use OpenApi\Processors\ProcessorInterface;

/**
 * Processor to clean up the generated OpenAPI documentation for nullable properties.
 */
final class NullablePropertyProcessor implements ProcessorInterface
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
                        $property->nullable = Generator::UNDEFINED;
                    }
                }
            }
        }
    }
}
