<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional;

use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Fairly intensive functional tests because the Kernel is recreated for each test.
 */
final class ControllerTest extends WebTestCase
{
    /**
     * @var ConfigurableContainerFactory
     */
    private $configurableContainerFactory;

    protected function setUp(): void
    {
        $this->configurableContainerFactory = new ConfigurableContainerFactory();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);
    }

    /**
     * @param array<mixed> $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new NelmioKernel([], null, []);
    }

    protected function getOpenApiDefinition(string $area = 'default'): OA\OpenApi
    {
        return $this->configurableContainerFactory->getContainer()->get(sprintf('nelmio_api_doc.generator.%s', $area))->generate();
    }

    /**
     * @dataProvider provideAnnotationTestCases
     * @dataProvider provideAttributeTestCases
     * @dataProvider provideUniversalTestCases
     *
     * @param array{name: string, type: string}|null $controller
     * @param string[]                               $extraConfigs
     */
    public function testControllers(?array $controller, ?string $fixtureName = null, array $extraConfigs = []): void
    {
        $controllerName = $controller['name'] ?? null;
        $controllerType = $controller['type'] ?? null;

        $fixtureName = $fixtureName ?? $controllerName ?? self::fail('A fixture name must be provided.');

        $routingConfiguration = function (RoutingConfigurator $routes) use ($controllerName, $controllerType) {
            if (null === $controllerName) {
                return;
            }

            $routes->withPath('/')->import(__DIR__."/Controller/$controllerName.php", $controllerType);
        };

        $this->configurableContainerFactory->create([], $routingConfiguration, $extraConfigs);

        $apiDefinition = $this->getOpenApiDefinition();

        // Create the fixture if it does not exist
        if (!file_exists($fixtureDir = __DIR__.'/Fixtures/'.$fixtureName.'.json')) {
            file_put_contents($fixtureDir, $apiDefinition->toJson());
        }

        self::assertSame(
            self::getFixture($fixtureDir),
            $this->getOpenApiDefinition()->toJson(),
        );
    }

    public static function provideAttributeTestCases(): \Generator
    {
        if (PHP_VERSION_ID < 80100) {
            return;
        }

        $type = Kernel::MAJOR_VERSION === 5 ? 'annotation' : 'attribute';

        yield 'Promoted properties defaults attributes' => [
            [
                'name' => 'PromotedPropertiesController81',
                'type' => $type,
            ],
            'PromotedPropertiesDefaults',
            [__DIR__.'/Configs/AlternativeNamesPHP81Entities.yaml'],
        ];

        if (version_compare(Kernel::VERSION, '6.3.0', '>=')) {
            yield 'https://github.com/nelmio/NelmioApiDocBundle/issues/2209' => [
                [
                    'name' => 'Controller2209',
                    'type' => $type,
                ],
            ];
            yield 'MapQueryString' => [
                [
                    'name' => 'MapQueryStringController',
                    'type' => $type,
                ],
            ];
            yield 'https://github.com/nelmio/NelmioApiDocBundle/issues/2191' => [
                [
                    'name' => 'MapQueryStringController',
                    'type' => $type,
                ],
                'MapQueryStringCleanupComponents',
                [__DIR__.'/Configs/CleanUnusedComponentsProcessor.yaml'],
            ];
        }
    }

    public static function provideAnnotationTestCases(): \Generator
    {
        if (!TestKernel::isAnnotationsAvailable()) {
            return;
        }

        if (PHP_VERSION_ID >= 80000) {
            yield 'Promoted properties defaults annotations' => [
                [
                    'name' => 'PromotedPropertiesController80',
                    'type' => 'annotation',
                ],
                'PromotedPropertiesDefaults',
                [__DIR__.'/Configs/AlternativeNamesPHP80Entities.yaml'],
            ];
        }
    }

    /**
     * Test cases that are universal and can be run without depending on the existence of a specific feature.
     */
    public static function provideUniversalTestCases(): \Generator
    {
        yield 'https://github.com/nelmio/NelmioApiDocBundle/issues/2224' => [
            null,
            'VendorExtension',
            [__DIR__.'/Configs/VendorExtension.yaml'],
        ];
    }

    private static function getFixture(string $fixture): string
    {
        if (!file_exists($fixture)) {
            self::fail(sprintf('The fixture file "%s" does not exist.', $fixture));
        }

        $content = file_get_contents($fixture);

        if (false === $content) {
            self::fail(sprintf('Failed to read the fixture file "%s".', $fixture));
        }

        return $content;
    }
}
