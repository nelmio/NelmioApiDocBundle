<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\RouteDescriber;

use Nelmio\ApiDocBundle\RouteDescriber\RouteMetadataDescriber;
use OpenApi\Annotations\OpenApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;

class RouteMetadataDescriberTest extends TestCase
{
    public function testUndefinedCheck()
    {
        $routeDescriber = new RouteMetadataDescriber();

        $this->assertNull($routeDescriber->describe(new OpenApi([]), new Route('foo'), new \ReflectionMethod(__CLASS__, 'testUndefinedCheck')));
    }
}
