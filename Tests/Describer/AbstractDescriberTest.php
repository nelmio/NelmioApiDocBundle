<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Describer;

use OpenApi\Annotations\OpenApi;
use PHPUnit\Framework\TestCase;

abstract class AbstractDescriberTest extends TestCase
{
    protected $describer;

    protected function getSwaggerDoc(): OpenApi
    {
        $api = new OpenApi([]);
        $this->describer->describe($api);

        return $api;
    }
}
