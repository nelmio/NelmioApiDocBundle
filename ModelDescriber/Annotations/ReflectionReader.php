<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\ModelDescriber\Annotations;

use OpenApi\Annotations as OA;
use OpenApi\Generator;

/**
 * @internal
 */
final class ReflectionReader
{
    public function updateProperty($reflection, OA\Property $property): void
    {
        // Make sure that a possibly set default value for a property is used, when not overwritten by an annotation
        // or attribute.
        if (!Generator::isDefault($property->default)) {
            return;
        }

        if (!$reflection instanceof \ReflectionProperty) {
            return;
        }

        if (PHP_VERSION_ID < 80000) {
            return;
        }

        if (!$reflection->hasDefaultValue()) {
            return;
        }

        $default = $reflection->getDefaultValue();
        if (null === $default) {
            return;
        }

        $property->default = $reflection->getDefaultValue();
    }
}
