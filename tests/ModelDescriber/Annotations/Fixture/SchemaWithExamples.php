<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\ModelDescriber\Annotations\Fixture;

use OpenApi\Attributes as OA;

/**
 * Uses the swagger-php 6.8.1+ attribute signature, so it lives outside PHPStan's paths.
 */
#[OA\Schema(examples: ['red', 'blue'])]
class SchemaWithExamples
{
    public string $name = 'coat';
}
