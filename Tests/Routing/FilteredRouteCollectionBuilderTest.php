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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Tests for FilteredRouteCollectionBuilder class.
 */
class FilteredRouteCollectionBuilderTest extends TestCase
{
    /**
     * @dataProvider routesProvider
     * @param RequestStack $requestStack
     * @param RouteCollection $routes
     * @param $expectedCount
     */
    public function testFilter($requestStack, $routes, $expectedCount)
    {
        $routesConfig = [
            ['host' => null, 'path_patterns' => ['^/api/foo', '^/api/bar']],
            ['host' => 'fakehost2.com', 'path_patterns' => ['^/api/demo']]
        ];

        $routeBuilder = new FilteredRouteCollectionBuilder($requestStack, $routesConfig);
        $filteredRoutes = $routeBuilder->filter($routes);

        $this->assertCount($expectedCount, $filteredRoutes);
    }

    public function routesProvider()
    {
        $result = [];

        $requestStack = new RequestStack();
        $fakeRequest = Request::create('/', 'GET');
        $fakeRequest->headers->add(['HOST' => 'fakehost.com']);
        $requestStack->push($fakeRequest);
        $routes = new RouteCollection();
        $routes->add('r1', new Route('/api/bar/action1'));
        $routes->add('r2', new Route('/api/foo/action1'));
        $routes->add('r3', new Route('/api/foo/action2'));
        $routes->add('r4', new Route('/api/demo'));
        $routes->add('r5', new Route('/_profiler/test/test'));

        $result[] = [$requestStack, $routes, 3];

        $requestStack = new RequestStack();
        $fakeRequest = Request::create('/', 'GET');
        $fakeRequest->headers->add(['HOST' => 'fakehost2.com']);
        $requestStack->push($fakeRequest);
        $routes = new RouteCollection();
        $routes->add('r1', new Route('/api/bar/action1'));
        $routes->add('r2', new Route('/api/foo/action1'));
        $routes->add('r3', new Route('/api/foo/action2', [], [], [], 'fakehost2.com'));
        $routes->add('r4', new Route('/api/demo', [], [], [], 'fakehost2.com'));
        $routes->add('r5', new Route('/_profiler/test/test'));

        $result[] = [$requestStack, $routes, 4];


        $requestStack = new RequestStack();
        $fakeRequest = Request::create('/', 'GET');
        $fakeRequest->headers->add(['HOST' => 'fakehost2.com']);
        $requestStack->push($fakeRequest);
        $routes = new RouteCollection();
        $routes->add('r1', new Route('/api/baz/action1'));
        $routes->add('r2', new Route('/api/you/shall/not/pass'));
        $routes->add('r4', new Route('/api/demo', [], [], [], 'fakehost2.com'));
        $routes->add('r5', new Route('/_profiler/test/test'));

        $result[] = [$requestStack, $routes, 1];

        return $result;
    }
}
