<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Routing;

use Nelmio\ApiDocBundle\Routing\FilteredRouteCollectionBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Tests for FilteredRouteCollectionBuilder class.
 */
class FilteredRouteCollectionBuilderTest extends TestCase
{
    public function testFilter()
    {
        $pathPattern = [
            '^/api/foo',
            '^/api/bar',
        ];

        $routes = new RouteCollection();
        $routes->add('r1', new Route('/api/bar/action1'));
        $routes->add('r2', new Route('/api/foo/action1'));
        $routes->add('r3', new Route('/api/foo/action2'));
        $routes->add('r4', new Route('/api/demo'));
        $routes->add('r5', new Route('/_profiler/test/test'));

        $routeBuilder = new FilteredRouteCollectionBuilder($pathPattern);
        $filteredRoutes = $routeBuilder->filter($routes);

        $this->assertCount(3, $filteredRoutes);
    }
}
