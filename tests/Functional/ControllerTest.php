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

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new NelmioKernel([], null, []);
    }

    protected function getOpenApiDefinition($area = 'default'): OA\OpenApi
    {
        return $this->configurableContainerFactory->getContainer()->get(sprintf('nelmio_api_doc.generator.%s', $area))->generate();
    }

    /**
     * @dataProvider provideControllers
     */
    public function testControllers(?string $controllerName, ?string $fixtureName = null, array $extraConfigs = []): void
    {
        $fixtureName = $fixtureName ?? $controllerName ?? $this->fail('A fixture name must be provided.');

        $routingConfiguration = function (RoutingConfigurator $routes) use ($controllerName) {
            if (null === $controllerName) {
                return;
            }

            $routes->withPath('/')->import(__DIR__."/Controller/$controllerName.php", 'attribute');
        };

        $this->configurableContainerFactory->create([], $routingConfiguration, $extraConfigs);

        $apiDefinition = $this->getOpenApiDefinition();

        // Create the fixture if it does not exist
        if (!file_exists($fixtureDir = __DIR__.'/Fixtures/'.$fixtureName.'.json')) {
            file_put_contents($fixtureDir, $apiDefinition->toJson());
        }

        self::assertSame(
            self::getFixture($fixtureDir),
            $this->getOpenApiDefinition()->toJson()
        );
    }

    public static function provideControllers(): iterable
    {
        if (version_compare(Kernel::VERSION, '6.3.0', '>=')) {
            yield 'https://github.com/nelmio/NelmioApiDocBundle/issues/2209' => ['Controller2209'];
            yield 'MapQueryString' => ['MapQueryStringController'];
            yield 'https://github.com/nelmio/NelmioApiDocBundle/issues/2191' => [
                'MapQueryStringController',
                'MapQueryStringCleanupComponents',
                [__DIR__.'/Configs/CleanUnusedComponentsProcessor.yaml'],
            ];
        }

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
