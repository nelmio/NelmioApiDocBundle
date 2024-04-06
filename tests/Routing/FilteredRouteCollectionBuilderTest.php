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
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Routing\FilteredRouteCollectionBuilder;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use OpenApi\Annotations\Parameter;
use OpenApi\Context;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Tests for FilteredRouteCollectionBuilder class.
 */
class FilteredRouteCollectionBuilderTest extends TestCase
{
    /**
     * @var AnnotationReader|null
     */
    private $doctrineAnnotations;

    protected function setUp(): void
    {
        $this->doctrineAnnotations = class_exists(AnnotationReader::class) ? new AnnotationReader() : null;
    }

    public function testFilter(): void
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
            $this->doctrineAnnotations,
            $this->createControllerReflector(),
            'areaName',
            $options
        );
        $filteredRoutes = $routeBuilder->filter($routes);

        self::assertCount(4, $filteredRoutes);
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation Passing an indexed array with a collection of path patterns as argument 1 for `Nelmio\ApiDocBundle\Routing\FilteredRouteCollectionBuilder::__construct()` is deprecated since 3.2.0, expected structure is an array containing parameterized options.
     */
    public function testFilterWithDeprecatedArgument(): void
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
            $this->doctrineAnnotations,
            $this->createControllerReflector(),
            'areaName',
            $pathPattern
        );
        $filteredRoutes = $routeBuilder->filter($routes);

        self::assertCount(5, $filteredRoutes);
    }

    /**
     * @dataProvider getInvalidOptions
     *
     * @param array<string, mixed> $options
     */
    public function testFilterWithInvalidOption(array $options): void
    {
        $this->expectException(InvalidArgumentException::class);

        new FilteredRouteCollectionBuilder(
            $this->doctrineAnnotations,
            $this->createControllerReflector(),
            'areaName',
            $options
        );
    }

    public static function getInvalidOptions(): \Generator
    {
        yield [['invalid_option' => null]];
        yield [['invalid_option' => 42]];
        yield [['invalid_option' => []]];
        yield [['path_patterns' => [22]]];
        yield [['path_patterns' => [null]]];
        yield [['path_patterns' => [new \stdClass()]]];
        yield [['path_patterns' => ['^/foo$', 1]]];
        yield [['with_annotation' => ['an array']]];
        yield [['path_patterns' => 'a string']];
        yield [['path_patterns' => 11]];
        yield [['name_patterns' => 22]];
        yield [['name_patterns' => 'a string']];
        yield [['name_patterns' => [22]]];
        yield [['name_patterns' => [null]]];
        yield [['name_patterns' => [new \stdClass()]]];
    }

    /**
     * @return array<string,Route>
     */
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
     *
     * @param array<string, mixed> $options
     */
    public function testMatchingRoutes(string $name, Route $route, array $options = []): void
    {
        $routes = new RouteCollection();
        $routes->add($name, $route);

        $routeBuilder = new FilteredRouteCollectionBuilder(
            $this->doctrineAnnotations,
            $this->createControllerReflector(),
            'area',
            $options
        );
        $filteredRoutes = $routeBuilder->filter($routes);

        self::assertCount(1, $filteredRoutes);
    }

    public static function getMatchingRoutes(): \Generator
    {
        yield from [
            ['r1', new Route('/api/bar/action1')],
            ['r2', new Route('/api/foo/action1'), ['path_patterns' => ['^/api', 'i/fo', 'n1$']]],
            ['r3', new Route('/api/foo/action2'), ['path_patterns' => ['^/api/foo/action2$']]],
            ['r4', new Route('/api/demo'), ['path_patterns' => ['/api/demo'], 'name_patterns' => ['r4']]],
            ['r9', new Route('/api/bar/action1', [], [], [], 'api.example.com'), ['path_patterns' => ['^/api/'], 'host_patterns' => ['^api\.ex']]],
            ['r10', new Route('/api/areas/new'), ['path_patterns' => ['^/api']]],
        ];

        if (\PHP_VERSION_ID < 80000) {
            yield ['r10', new Route('/api/areas_attributes/new'), ['path_patterns' => ['^/api']]];
        }
    }

    /**
     * @group test
     *
     * @dataProvider getMatchingRoutesWithAnnotation
     *
     * @param array<string, mixed> $options
     */
    public function testMatchingRoutesWithAnnotation(string $name, Route $route, array $options = []): void
    {
        $routes = new RouteCollection();
        $routes->add($name, $route);
        $area = 'area';

        $reflectionMethodStub = $this->createMock(\ReflectionMethod::class);
        $controllerReflectorStub = $this->createMock(ControllerReflector::class);
        $controllerReflectorStub->method('getReflectionMethod')->willReturn($reflectionMethodStub);

        $annotationReader = null;
        if (interface_exists(Reader::class)) {
            $annotationReader = $this->createMock(Reader::class);
            $annotationReader
                ->method('getMethodAnnotation')
                ->with($reflectionMethodStub, Areas::class)
                ->willReturn(new Areas(['value' => [$area]]))
            ;
        }

        $routeBuilder = new FilteredRouteCollectionBuilder(
            $annotationReader,
            $controllerReflectorStub,
            $area,
            $options
        );
        $filteredRoutes = $routeBuilder->filter($routes);

        self::assertCount(1, $filteredRoutes);
    }

    public static function getMatchingRoutesWithAnnotation(): \Generator
    {
        yield from [
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

        if (\PHP_VERSION_ID < 80000) {
            yield from [
                'with attribute only' => [
                    'r10',
                    new Route('/api/areas_attributes/new', ['_controller' => 'ApiController::newAreaActionAttributes']),
                    ['with_annotation' => true],
                ],
                'with attribute and path patterns' => [
                    'r10',
                    new Route('/api/areas_attributes/new', ['_controller' => 'ApiController::newAreaActionAttributes']),
                    ['path_patterns' => ['^/api'], 'with_annotation' => true],
                ],
            ];
        }
    }

    /**
     * @dataProvider getNonMatchingRoutes
     *
     * @param array<string, mixed> $options
     */
    public function testNonMatchingRoutes(string $name, Route $route, array $options = []): void
    {
        $routes = new RouteCollection();
        $routes->add($name, $route);

        $routeBuilder = new FilteredRouteCollectionBuilder(
            $this->doctrineAnnotations,
            $this->createControllerReflector(),
            'areaName',
            $options
        );
        $filteredRoutes = $routeBuilder->filter($routes);

        self::assertCount(0, $filteredRoutes);
    }

    public static function getNonMatchingRoutes(): \Generator
    {
        yield ['r1', new Route('/api/bar/action1'), ['path_patterns' => ['^/apis']]];
        yield ['r2', new Route('/api/foo/action1'), ['path_patterns' => ['^/apis', 'i/foo/b', 'n1/$'], 'name_patterns' => ['r2']]];
        yield ['r3_matching_path_and_non_matching_host', new Route('/api/foo/action2'), ['path_patterns' => ['^/api/foo/action2$'], 'host_patterns' => ['^api\.']]];
        yield ['r4_matching_path_and_non_matching_host', new Route('/api/bar/action1', [], [], [], 'www.example.com'), ['path_patterns' => ['^/api/'], 'host_patterns' => ['^api\.']]];
        yield ['r5_non_matching_path_and_matching_host', new Route('/admin/bar/action1', [], [], [], 'api.example.com'), ['path_patterns' => ['^/api/'], 'host_patterns' => ['^api\.']]];
        yield ['r6_non_matching_path_and_non_matching_host', new Route('/admin/bar/action1', [], [], [], 'www.example.com'), ['path_patterns' => ['^/api/'], 'host_patterns' => ['^api\.ex']]];
    }

    /**
     * @dataProvider getRoutesWithDisabledDefaultRoutes
     *
     * @param array<Operation|Parameter> $annotations
     * @param array<string|bool>         $options
     */
    public function testRoutesWithDisabledDefaultRoutes(
        string $name,
        Route $route,
        array $annotations,
        array $options,
        int $expectedRoutesCount
    ): void {
        $routes = new RouteCollection();
        $routes->add($name, $route);
        $area = 'area';

        $reflectionMethodStub = $this->createMock(\ReflectionMethod::class);
        $controllerReflectorStub = $this->createMock(ControllerReflector::class);
        $controllerReflectorStub->method('getReflectionMethod')->willReturn($reflectionMethodStub);

        $annotationReader = null;
        if (interface_exists(Reader::class)) {
            $annotationReader = $this->createMock(Reader::class);
            $annotationReader
                ->method('getMethodAnnotations')
                ->willReturn($annotations)
            ;
        }

        $routeBuilder = new FilteredRouteCollectionBuilder(
            $annotationReader,
            $controllerReflectorStub,
            $area,
            $options
        );
        $filteredRoutes = $routeBuilder->filter($routes);

        self::assertCount($expectedRoutesCount, $filteredRoutes);
    }

    public static function getRoutesWithDisabledDefaultRoutes(): \Generator
    {
        yield 'non matching route without Annotation' => [
            'r10',
            new Route('/api/foo', ['_controller' => 'ApiController::fooAction']),
            [],
            ['disable_default_routes' => true],
            0,
        ];
        yield 'matching route with Nelmio Annotation' => [
            'r10',
            new Route('/api/foo', ['_controller' => 'ApiController::fooAction']),
            [new Operation(['_context' => new Context()])],
            ['disable_default_routes' => true],
            1,
        ];
        yield 'matching route with Swagger Annotation' => [
            'r10',
            new Route('/api/foo', ['_controller' => 'ApiController::fooAction']),
            [new Parameter(['_context' => new Context()])],
            ['disable_default_routes' => true],
            1,
        ];
    }

    private function createControllerReflector(): ControllerReflector
    {
        return new ControllerReflector(new Container());
    }
}
