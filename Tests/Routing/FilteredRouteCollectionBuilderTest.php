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

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Nelmio\ApiDocBundle\Annotation\Areas;
use Nelmio\ApiDocBundle\Routing\FilteredRouteCollectionBuilder;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Tests for FilteredRouteCollectionBuilder class.
 */
class FilteredRouteCollectionBuilderTest extends TestCase
{
    public function testFilter()
    {
        $options = [
            'path_patterns' => [
                '^/api/foo',
                '^/api/bar',
            ],
            'host_patterns' => [
                '^$',
                '^api\.',
            ],
        ];

        $routes = new RouteCollection();
        foreach ($this->getRoutes() as $name => $route) {
            $routes->add($name, $route);
        }

        $routeBuilder = new FilteredRouteCollectionBuilder(
            new AnnotationReader(),
            $this->createControllerReflector(),
            'areaName',
            $options
        );
        $filteredRoutes = $routeBuilder->filter($routes);

        $this->assertCount(4, $filteredRoutes);
    }

    /**
     * @group legacy
     * @expectedDeprecation Passing an indexed array with a collection of path patterns as argument 1 for `Nelmio\ApiDocBundle\Routing\FilteredRouteCollectionBuilder::__construct()` is deprecated since 3.2.0, expected structure is an array containing parameterized options.
     */
    public function testFilterWithDeprecatedArgument()
    {
        $pathPattern = [
            '^/api/foo',
            '^/api/bar',
        ];

        $routes = new RouteCollection();
        foreach ($this->getRoutes() as $name => $route) {
            $routes->add($name, $route);
        }

        $routeBuilder = new FilteredRouteCollectionBuilder(
            new AnnotationReader(),
            $this->createControllerReflector(),
            'areaName',
            $pathPattern
        );
        $filteredRoutes = $routeBuilder->filter($routes);

        $this->assertCount(5, $filteredRoutes);
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidArgumentException
     *
     * @dataProvider getInvalidOptions
     */
    public function testFilterWithInvalidOption(array $options)
    {
        new FilteredRouteCollectionBuilder(
            new AnnotationReader(),
            $this->createControllerReflector(),
            'areaName',
            $options
        );
    }

    public function getInvalidOptions(): array
    {
        return [
            [['invalid_option' => null]],
            [['invalid_option' => 42]],
            [['invalid_option' => []]],
            [['path_patterns' => [22]]],
            [['path_patterns' => [null]]],
            [['path_patterns' => [new \stdClass()]]],
            [['path_patterns' => ['^/foo$', 1]]],
            [['with_annotation' => ['an array']]],
            [['path_patterns' => 'a string']],
            [['path_patterns' => 11]],
            [['name_patterns' => 22]],
            [['name_patterns' => 'a string']],
            [['name_patterns' => [22]]],
            [['name_patterns' => [null]]],
            [['name_patterns' => [new \stdClass()]]],
        ];
    }

    private function getRoutes(): array
    {
        return [
            'r1' => new Route('/api/bar/action1'),
            'r2' => new Route('/api/foo/action1'),
            'r3' => new Route('/api/foo/action2'),
            'r4' => new Route('/api/demo'),
            'r5' => new Route('/_profiler/test/test'),
            'r6' => new Route('/admin/bar/action1', [], [], [], 'www.example.com'),
            'r7' => new Route('/api/bar/action1', [], [], [], 'www.example.com'),
            'r8' => new Route('/admin/bar/action1', [], [], [], 'api.example.com'),
            'r9' => new Route('/api/bar/action1', [], [], [], 'api.example.com'),
            'r10' => new Route('/api/areas/new'),
        ];
    }

    /**
     * @dataProvider getMatchingRoutes
     */
    public function testMatchingRoutes(string $name, Route $route, array $options = [])
    {
        $routes = new RouteCollection();
        $routes->add($name, $route);

        $routeBuilder = new FilteredRouteCollectionBuilder(
            new AnnotationReader(),
            $this->createControllerReflector(),
            'area',
            $options
        );
        $filteredRoutes = $routeBuilder->filter($routes);

        $this->assertCount(1, $filteredRoutes);
    }

    public function getMatchingRoutes(): array
    {
        return [
            ['r1', new Route('/api/bar/action1')],
            ['r2', new Route('/api/foo/action1'), ['path_patterns' => ['^/api', 'i/fo', 'n1$']]],
            ['r3', new Route('/api/foo/action2'), ['path_patterns' => ['^/api/foo/action2$']]],
            ['r4', new Route('/api/demo'), ['path_patterns' => ['/api/demo'], 'name_patterns' => ['r4']]],
            ['r9', new Route('/api/bar/action1', [], [], [], 'api.example.com'), ['path_patterns' => ['^/api/'], 'host_patterns' => ['^api\.ex']]],
            ['r10', new Route('/api/areas/new'), ['path_patterns' => ['^/api']]],
        ];
    }

    /**
     * @group test
     * @dataProvider getMatchingRoutesWithAnnotation
     */
    public function testMatchingRoutesWithAnnotation(string $name, Route $route, array $options = [])
    {
        $routes = new RouteCollection();
        $routes->add($name, $route);
        $area = 'area';

        $reflectionMethodStub = $this->createMock(\ReflectionMethod::class);
        $controllerReflectorStub = $this->createMock(ControllerReflector::class);
        $controllerReflectorStub->method('getReflectionMethod')->willReturn($reflectionMethodStub);

        $annotationReader = $this->createMock(Reader::class);
        $annotationReader
            ->method('getMethodAnnotation')
            ->with($reflectionMethodStub, Areas::class)
            ->willReturn(new Areas(['value' => [$area]]))
        ;

        $routeBuilder = new FilteredRouteCollectionBuilder(
            $annotationReader,
            $controllerReflectorStub,
            $area,
            $options
        );
        $filteredRoutes = $routeBuilder->filter($routes);

        $this->assertCount(1, $filteredRoutes);
    }

    public function getMatchingRoutesWithAnnotation(): array
    {
        return [
            'with annotation only' => [
                'r10',
                new Route('/api/areas/new', ['_controller' => 'ApiController::newAreaAction']),
                ['with_annotation' => true],
            ],
            'with annotation and path patterns' => [
                'r10',
                new Route('/api/areas/new', ['_controller' => 'ApiController::newAreaAction']),
                ['path_patterns' => ['^/api'], 'with_annotation' => true],
            ],
        ];
    }

    /**
     * @dataProvider getNonMatchingRoutes
     */
    public function testNonMatchingRoutes(string $name, Route $route, array $options = [])
    {
        $routes = new RouteCollection();
        $routes->add($name, $route);

        $routeBuilder = new FilteredRouteCollectionBuilder(
            new AnnotationReader(),
            $this->createControllerReflector(),
            'areaName',
            $options
        );
        $filteredRoutes = $routeBuilder->filter($routes);

        $this->assertCount(0, $filteredRoutes);
    }

    public function getNonMatchingRoutes(): array
    {
        return [
            ['r1', new Route('/api/bar/action1'), ['path_patterns' => ['^/apis']]],
            ['r2', new Route('/api/foo/action1'), ['path_patterns' => ['^/apis', 'i/foo/b', 'n1/$'], 'name_patterns' => ['r2']]],
            ['r3_matching_path_and_non_matching_host', new Route('/api/foo/action2'), ['path_patterns' => ['^/api/foo/action2$'], 'host_patterns' => ['^api\.']]],
            ['r4_matching_path_and_non_matching_host', new Route('/api/bar/action1', [], [], [], 'www.example.com'), ['path_patterns' => ['^/api/'], 'host_patterns' => ['^api\.']]],
            ['r5_non_matching_path_and_matching_host', new Route('/admin/bar/action1', [], [], [], 'api.example.com'), ['path_patterns' => ['^/api/'], 'host_patterns' => ['^api\.']]],
            ['r6_non_matching_path_and_non_matching_host', new Route('/admin/bar/action1', [], [], [], 'www.example.com'), ['path_patterns' => ['^/api/'], 'host_patterns' => ['^api\.ex']]],
        ];
    }

    private function createControllerReflector(): ControllerReflector
    {
        if (class_exists(ControllerNameParser::class)) {
            return new ControllerReflector(
                new Container(),
                $this->createMock(ControllerNameParser::class)
            );
        }

        return  new ControllerReflector(new Container());
    }
}
