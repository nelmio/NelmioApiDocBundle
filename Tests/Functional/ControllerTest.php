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
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

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
     * @dataProvider provideIssueTests
     */
    public function testIssues(string $testName, array $extraConfigs = []): void
    {
        $routingConfiguration = function (RoutingConfigurator $routes) use ($testName) {
            $routes->withPath('/')->import(__DIR__."/Controller/$testName.php", 'attribute');
        };

        $this->configurableContainerFactory->create([], $routingConfiguration, $extraConfigs);

        $apiDefinition = $this->getOpenApiDefinition();

        if (!file_exists($fixtureDir = __DIR__.'/Fixtures/'.$testName.'.json')) {
            file_put_contents($fixtureDir, $apiDefinition->toJson());
        }

        self::assertSame(
            self::getFixture($fixtureDir),
            $this->getOpenApiDefinition()->toJson()
        );
    }

    public static function provideIssueTests(): iterable
    {
        yield 'https://github.com/nelmio/NelmioApiDocBundle/issues/2209' => ['Controller2209'];
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
