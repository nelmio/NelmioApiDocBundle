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

use Nelmio\ApiDocBundle\Attribute\Areas;
use Nelmio\ApiDocBundle\Attribute\Operation;
use Nelmio\ApiDocBundle\Routing\FilteredRouteCollectionBuilder;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use OpenApi\Attributes\Parameter;
use OpenApi\Context;
use PHPUnit\Framework\Attributes\DataProvider;
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
            $this->createControllerReflector(),
            'areaName',
            $options
        );
        $filteredRoutes = $routeBuilder->filter($routes);

        self::assertCount(4, $filteredRoutes);
    }

    /**
     * @param array<string, mixed> $options
     */
    #[DataProvider('getInvalidOptions')]
    public function testFilterWithInvalidOption(array $options): void
    {
        $this->expectException(InvalidArgumentException::class);

        new FilteredRouteCollectionBuilder(
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
        yield [['with_attribute' => ['an array']]];
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
     * @param array<string, mixed> $options
     */
    #[DataProvider('getMatchingRoutes')]
    public function testMatchingRoutes(string $name, Route $route, array $options = []): void
    {
        $routes = new RouteCollection();
        $routes->add($name, $route);

        $routeBuilder = new FilteredRouteCollectionBuilder(
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

        yield ['r10', new Route('/api/areas_attributes/new'), ['path_patterns' => ['^/api']]];
    }

    /**
     * @param array<string, mixed> $options
     */
    #[DataProvider('getMatchingRoutesWithAnnotation')]
    public function testMatchingRoutesWithAnnotation(string $name, Route $route, \ReflectionMethod $reflectionMethod, array $options = []): void
    {
        $routes = new RouteCollection();
        $routes->add($name, $route);
        $area = 'area';

        $controllerReflectorStub = $this->createMock(ControllerReflector::class);
        $controllerReflectorStub->method('getReflectionMethod')->willReturn($reflectionMethod);

        $routeBuilder = new FilteredRouteCollectionBuilder(
            $controllerReflectorStub,
            $area,
            $options
        );
        $filteredRoutes = $routeBuilder->filter($routes);

        self::assertCount(1, $filteredRoutes);
    }

    public static function getMatchingRoutesWithAnnotation(): \Generator
    {
        $apiController = new class {
            #[Areas(['area'])]
            public function fooAction(): void
            {
            }
        };

        yield from [
            'with attribute only' => [
                'r10',
                new Route('/api/areas_attributes/new', ['_controller' => 'ApiController::newAreaActionAttributes']),
                new \ReflectionMethod($apiController, 'fooAction'),
                ['with_attribute' => true],
            ],
            'with attribute and path patterns' => [
                'r10',
                new Route('/api/areas_attributes/new', ['_controller' => 'ApiController::newAreaActionAttributes']),
                new \ReflectionMethod($apiController, 'fooAction'),
                ['path_patterns' => ['^/api'], 'with_attribute' => true],
            ],
        ];

        $apiController = new #[Areas(['area'])] class {
            public function fooAction(): void
            {
            }
        };

        yield 'with class attribute only' => [
            'r10',
            new Route('/api/areas_attributes/new', ['_controller' => 'ApiController::newAreaActionAttributes']),
            new \ReflectionMethod($apiController, 'fooAction'),
            ['with_attribute' => true],
        ];
    }

    /**
     * @param array<string, mixed> $options
     */
    #[DataProvider('getNonMatchingRoutes')]
    public function testNonMatchingRoutes(string $name, Route $route, array $options = []): void
    {
        $routes = new RouteCollection();
        $routes->add($name, $route);

        $routeBuilder = new FilteredRouteCollectionBuilder(
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
     * @param array<string|bool> $options
     */
    #[DataProvider('getRoutesWithDisabledDefaultRoutes')]
    public function testRoutesWithDisabledDefaultRoutes(
        string $name,
        Route $route,
        \ReflectionMethod $reflectionMethod,
        array $options,
        int $expectedRoutesCount,
    ): void {
        $routes = new RouteCollection();
        $routes->add($name, $route);
        $area = 'area';

        $controllerReflectorStub = $this->createMock(ControllerReflector::class);
        $controllerReflectorStub->method('getReflectionMethod')->willReturn($reflectionMethod);

        $routeBuilder = new FilteredRouteCollectionBuilder(
            $controllerReflectorStub,
            $area,
            $options
        );
        $filteredRoutes = $routeBuilder->filter($routes);

        self::assertCount($expectedRoutesCount, $filteredRoutes);
    }

    public static function getRoutesWithDisabledDefaultRoutes(): \Generator
    {
        $apiController = new class {
            public function fooAction(): void
            {
            }
        };

        yield 'non matching route without Annotation' => [
            'r10',
            new Route('/api/foo', ['_controller' => 'ApiController::fooAction']),
            new \ReflectionMethod($apiController, 'fooAction'),
            ['disable_default_routes' => true],
            0,
        ];

        yield 'no area defined' => [
            'r10',
            new Route('/api/foo', ['_controller' => 'ApiController::fooAction']),
            new \ReflectionMethod($apiController, 'fooAction'),
            ['with_attribute' => true],
            0,
        ];

        $apiController = new class {
            #[Areas(['area_something_very_different'])]
            public function fooAction(): void
            {
            }
        };

        yield 'non matching route with different method area Annotation' => [
            'r10',
            new Route('/api/foo', ['_controller' => 'ApiController::fooAction']),
            new \ReflectionMethod($apiController, 'fooAction'),
            ['disable_default_routes' => true],
            0,
        ];

        $apiController = new #[Areas(['area_something_very_different'])] class {
            public function fooAction(): void
            {
            }
        };

        yield 'non matching route with different class area Annotation' => [
            'r10',
            new Route('/api/foo', ['_controller' => 'ApiController::fooAction']),
            new \ReflectionMethod($apiController, 'fooAction'),
            ['disable_default_routes' => true],
            0,
        ];

        $apiController = new class {
            #[Operation(['_context' => new Context()])]
            public function fooAction(): void
            {
            }
        };
        yield 'matching route with Nelmio Annotation' => [
            'r10',
            new Route('/api/foo', ['_controller' => 'ApiController::fooAction']),
            new \ReflectionMethod($apiController, 'fooAction'),
            ['disable_default_routes' => true],
            1,
        ];

        $apiController = new class {
            #[Parameter]
            public function fooAction(): void
            {
            }
        };
        yield 'matching route with Swagger Annotation' => [
            'r10',
            new Route('/api/foo', ['_controller' => 'ApiController::fooAction']),
            new \ReflectionMethod($apiController, 'fooAction'),
            ['disable_default_routes' => true],
            1,
        ];
    }

    public function testRoutesWithInvalidController(): void
    {
        $routes = new RouteCollection();
        $routes->add('foo', new Route('/api/foo', ['_controller' => 'ApiController::fooAction']));

        $controllerReflectorStub = $this->createMock(ControllerReflector::class);
        $controllerReflectorStub
            ->expects(self::once())
            ->method('getReflectionMethod')
            ->with('ApiController::fooAction')
            ->willReturn(null);

        $routeBuilder = new FilteredRouteCollectionBuilder(
            $controllerReflectorStub,
            'area',
            ['with_attribute' => true],
        );
        $filteredRoutes = $routeBuilder->filter($routes);

        self::assertCount(0, $filteredRoutes);
    }

    private function createControllerReflector(): ControllerReflector
    {
        return new ControllerReflector(new Container());
    }
}
