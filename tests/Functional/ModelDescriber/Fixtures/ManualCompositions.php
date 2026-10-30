<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\Fixtures;

use OpenApi\Attributes as OA;

class ManualCompositions
{
    public function __construct(
        #[OA\Property(oneOf: [
            new OA\Schema(title: 'title for int', type: 'integer'),
            new OA\Schema(title: 'title for string', type: 'string'),
        ])]
        public int|string $oneOf,
        #[OA\Property(anyOf: [
            new OA\Schema(type: 'integer', minimum: 1),
            new OA\Schema(type: 'string', minLength: 1),
        ])]
        public int|string $anyOf,
        #[OA\Property(description: 'A referenced class', allOf: [
            new OA\Schema(ref: '#/components/schemas/SomeRefClass'),
        ])]
        public SomeRefClass $allOf,
    ) {
    }
}
