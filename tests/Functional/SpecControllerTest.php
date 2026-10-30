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

use Nelmio\ApiDocBundle\Describer\OperationIdGeneration;
use Nelmio\ApiDocBundle\Describer\SpecAttributeDescriber;
use OpenApi\Annotations as OA;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Controllers written with swagger-php spec attributes, under Controller/Spec/. Each is the twin of
 * a classic controller and has to produce its fixture.
 */
final class SpecControllerTest extends WebTestCase
{
    use ComparesOpenApiFixturesTrait;

    private ConfigurableContainerFactory $configurableContainerFactory;

    protected function setUp(): void
    {
        if (!SpecAttributeDescriber::isSupported()) {
            self::markTestSkipped('Spec attributes need swagger-php 6.17 or later.');
        }

        $this->configurableContainerFactory = new ConfigurableContainerFactory();
    }

    protected function getOpenApiDefinition(string $area = 'default'): OA\OpenApi
    {
        return $this->configurableContainerFactory->getContainer()->get(\sprintf('nelmio_api_doc.generator.%s', $area))->generate();
    }

    /**
     * A controller written with spec attributes produces the fixture of its classic twin.
     *
     * @param array<string, array<mixed>> $extraConfigs Key is the extension name, value is the config
     */
    #[DataProvider('provideTestCases')]
    public function testControllers(string $controller, string $fixtureName, array $extraConfigs = []): void
    {
        $routingConfiguration = static function (RoutingConfigurator &$routes) use ($controller) {
            $routes->withPath('/')->import(__DIR__."/Controller/Spec/$controller.php", 'attribute');
        };

        $this->configurableContainerFactory->create([], $routingConfiguration, $extraConfigs, []);

        $expected = json_decode(self::getFixture(__DIR__.'/Fixtures/'.$fixtureName.'.json'), false, 512, \JSON_THROW_ON_ERROR);
        $actual = LegacyNullable::upgrade(json_decode($this->getOpenApiDefinition()->toJson(), false, 512, \JSON_THROW_ON_ERROR));

        self::assertEquals(
            self::normalizeOpenApiDecodedForComparison($expected),
            self::normalizeOpenApiDecodedForComparison($actual),
        );
    }

    public static function provideTestCases(): \Generator
    {
        yield 'Custom model names' => [
            'CustomModelNameController',
            'CustomModelNameController',
        ];

        yield 'Custom model names, OpenAPI 3.1' => [
            'CustomModelNameController',
            'CustomModelNameController.openapi31',
            [
                'nelmio_api_doc' => [
                    'documentation' => ['openapi' => '3.1.0'],
                ],
            ],
        ];

        yield 'Create top level Tag from Tag attribute' => [
            'OpenApiTagController',
            'OpenApiTagController',
        ];

        yield 'Tag from class on inherited controller' => [
            'InvoiceDocumentController',
            'InvoiceDocumentController',
        ];

        yield 'operationId must always be generated' => [
            'OperationIdController',
            'OperationIdController',
        ];

        yield 'operationId generation conditionally_prepend' => [
            'OperationIdController',
            'OperationIdController.conditionally_prepend',
            [
                'nelmio_api_doc' => [
                    'operation_id_generation' => OperationIdGeneration::CONDITIONALLY_PREPEND,
                ],
            ],
        ];

        yield 'operationId generation no_prepend' => [
            'OperationIdController',
            'OperationIdController.no_prepend',
            [
                'nelmio_api_doc' => [
                    'operation_id_generation' => OperationIdGeneration::NO_PREPEND,
                ],
            ],
        ];

        yield 'Invokable controller, with security' => [
            'InvokableController',
            'InvokableController.insecured',
            [
                'nelmio_api_doc' => [
                    'areas' => [
                        'default' => [
                            'security' => [
                                'BearerAuth' => [
                                    'type' => 'http',
                                    'scheme' => 'bearer',
                                    'bearerFormat' => 'JWT',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'Route variants, classic and spec attributes on one method' => [
            'RouteVariantsController',
            'RouteVariantsController',
        ];
    }
}
