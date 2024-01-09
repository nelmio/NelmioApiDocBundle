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

use OpenApi\Annotations as OA;
use OpenApi\Generator;

final class NullablePropertyDescriber implements PropertyDescriberInterface, PropertyDescriberAwareInterface
{
    use PropertyDescriberAwareTrait;

    private const RECURSIVE = self::class.'::RECURSIVE';

    public function describe(array $types, OA\Schema $property, array $groups = null, ?OA\Schema $schema = null, array $context = [])
    {
        if (Generator::UNDEFINED !== $property->nullable) {
            return;
        }

        $property->nullable = true;

        $context[self::RECURSIVE] = true;
        $this->propertyDescriber->describe($types, $property, $groups, $schema, $context);
    }

    public function supports(array $types, array $context = []): bool
    {
        if (array_key_exists(self::RECURSIVE, $context)) {
            return false;
        }

        foreach ($types as $type) {
            if ($type->isNullable()) {
                return true;
            }
        }

        return false;
    }
}
