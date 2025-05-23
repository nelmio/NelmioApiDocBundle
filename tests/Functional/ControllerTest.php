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

use JMS\SerializerBundle\JMSSerializerBundle;
use OpenApi\Annotations as OA;
use OpenApi\Processors\CleanUnusedComponents;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;
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
    }

    protected function getOpenApiDefinition(string $area = 'default'): OA\OpenApi
    {
        return $this->configurableContainerFactory->getContainer()->get(\sprintf('nelmio_api_doc.generator.%s', $area))->generate();
    }

    /**
     * @param Bundle[] $extraBundles
     * @param string[] $extraConfigs
     */
    #[DataProvider('provideTestCases')]
    public function testControllers(?string $controller, ?string $fixtureName = null, array $extraBundles = [], array $extraConfigs = []): void
    {
        $fixtureName ??= $controller ?? self::fail('A fixture name must be provided.');

        $routingConfiguration = function (RoutingConfigurator $routes) use ($controller) {
            if (null === $controller) {
                return;
            }

            $routes->withPath('/')->import(__DIR__."/Controller/$controller.php", 'attribute');
        };

        $this->configurableContainerFactory->create($extraBundles, $routingConfiguration, $extraConfigs);

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

    public static function provideTestCases(): \Generator
    {
        yield 'Promoted properties defaults attributes' => [
            'PromotedPropertiesController81',
            'PromotedPropertiesDefaults',
            [],
            [...self::cleanUnusedComponentsConfig()],
        ];

        yield 'JMS model opt out' => [
            'JmsOptOutController',
            'JmsOptOutController',
            [new JMSSerializerBundle()],
            [__DIR__.'/Configs/JMS.yaml'],
        ];

        yield 'https://github.com/nelmio/NelmioApiDocBundle/issues/2209' => [
            'Controller2209',
        ];

        yield 'MapQueryString' => [
            'MapQueryStringController',
            null,
            [],
            [__DIR__.'/Configs/EnableSerializer.yaml'],
        ];

        yield 'https://github.com/nelmio/NelmioApiDocBundle/issues/2191' => [
            'MapQueryStringController',
            'MapQueryStringCleanupComponents',
            [],
            [__DIR__.'/Configs/CleanUnusedComponentsProcessor.yaml', __DIR__.'/Configs/EnableSerializer.yaml'],
        ];

        yield 'operationId must always be generated' => [
            'OperationIdController',
        ];

        yield 'Symfony 6.3 MapQueryParameter attribute' => [
            'MapQueryParameterController',
        ];

        yield 'Symfony 6.3 MapRequestPayload attribute' => [
            'MapRequestPayloadController',
            null,
            [],
            [__DIR__.'/Configs/EnableSerializer.yaml'],
        ];

        yield 'Create top level Tag from Tag attribute' => [
            'OpenApiTagController',
        ];

        if (property_exists(MapRequestPayload::class, 'type')) {
            yield 'Symfony 7.1 MapRequestPayload array type' => [
                'MapRequestPayloadArray',
            ];
        }

        if (version_compare(Kernel::VERSION, '7.1.0', '>=')) {
            yield 'Symfony 7.1 MapUploadedFile attribute' => [
                'MapUploadedFileController',
            ];
        }

        yield 'https://github.com/nelmio/NelmioApiDocBundle/issues/2224' => [
            null,
            'VendorExtension',
            [],
            [__DIR__.'/Configs/VendorExtension.yaml', __DIR__.'/Configs/StubProcessor.yaml'],
        ];
    }

    private static function getFixture(string $fixture): string
    {
        if (!file_exists($fixture)) {
            self::fail(\sprintf('The fixture file "%s" does not exist.', $fixture));
        }

        $content = file_get_contents($fixture);

        if (false === $content) {
            self::fail(\sprintf('Failed to read the fixture file "%s".', $fixture));
        }

        return $content;
    }

    /**
     * @return string[]
     */
    private static function cleanUnusedComponentsConfig(): array
    {
        /* @phpstan-ignore-next-line */
        if (method_exists(CleanUnusedComponents::class, 'setEnabled')) {
            return [__DIR__.'/Configs/CleanUnusedComponentsProcessor.yaml'];
        }

        return [__DIR__.'/Configs/CleanUnusedComponentsProcessorNoSetter.yaml'];
    }
}
