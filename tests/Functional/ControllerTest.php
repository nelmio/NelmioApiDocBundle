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
use Nelmio\ApiDocBundle\Describer\OperationIdGeneration;
use OpenApi\Annotations as OA;
use OpenApi\Processors\CleanUnusedComponents;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DependencyInjection\Definition;
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
     * @param Bundle[]                    $extraBundles
     * @param array<string, array<mixed>> $extraConfigs     Key is the extension name, value is the config
     * @param array<string, Definition>   $extraDefinitions
     */
    #[DataProvider('provideTestCases')]
    public function testControllers(?string $controller, ?string $fixtureName = null, array $extraBundles = [], array $extraConfigs = [], array $extraDefinitions = []): void
    {
        $fixtureName ??= $controller ?? self::fail('A fixture name must be provided.');

        $routingConfiguration = function (RoutingConfigurator $routes) use ($controller) {
            if (null === $controller) {
                return;
            }

            $routes->withPath('/')->import(__DIR__."/Controller/$controller.php", 'attribute');
        };

        $this->configurableContainerFactory->create($extraBundles, $routingConfiguration, $extraConfigs, $extraDefinitions);

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
            [],
            [
                CleanUnusedComponents::class => (new Definition(CleanUnusedComponents::class))
                    ->addTag('nelmio_api_doc.swagger.processor', ['priority' => -100])
                    ->addMethodCall('setEnabled', [true]),
            ],
        ];

        yield 'JMS model opt out' => [
            'JmsOptOutController',
            'JmsOptOutController',
            [new JMSSerializerBundle()],
            [
                'nelmio_api_doc' => [
                    'models' => [
                        'use_jms' => true,
                    ],
                ],
            ],
        ];

        yield 'https://github.com/nelmio/NelmioApiDocBundle/issues/2209' => [
            'Controller2209',
        ];

        yield 'MapQueryString' => [
            'MapQueryStringController',
            null,
            [],
            [
                // Enable serializer
                'framework' => [
                    'property_info' => [
                        'enabled' => true,
                    ],
                    'serializer' => [
                        'enabled' => true,
                        'enable_attributes' => true,
                    ],
                    'validation' => [
                        'enabled' => true,
                        'enable_attributes' => true,
                        'static_method' => [
                            'loadValidatorMetadata',
                        ],
                        'translation_domain' => 'validators',
                        'email_validation_mode' => 'html5',
                        'mapping' => [
                            'paths' => [],
                        ],
                        'not_compromised_password' => [
                            'enabled' => true,
                            'endpoint' => null,
                        ],
                        'auto_mapping' => [],
                    ],
                ],
            ],
        ];

        yield 'https://github.com/nelmio/NelmioApiDocBundle/issues/2191' => [
            'MapQueryStringController',
            'MapQueryStringCleanupComponents',
            [],
            [
                // Enable serializer
                'framework' => [
                    'property_info' => [
                        'enabled' => true,
                    ],
                    'serializer' => [
                        'enabled' => true,
                        'enable_attributes' => true,
                    ],
                    'validation' => [
                        'enabled' => true,
                        'enable_attributes' => true,
                        'static_method' => [
                            'loadValidatorMetadata',
                        ],
                        'translation_domain' => 'validators',
                        'email_validation_mode' => 'html5',
                        'mapping' => [
                            'paths' => [],
                        ],
                        'not_compromised_password' => [
                            'enabled' => true,
                            'endpoint' => null,
                        ],
                        'auto_mapping' => [],
                    ],
                ],
            ],
            [
                CleanUnusedComponents::class => (new Definition(CleanUnusedComponents::class))
                    ->addTag('nelmio_api_doc.swagger.processor', ['priority' => -100])
                    ->addMethodCall('setEnabled', [true]),
            ],
        ];

        yield 'operationId must always be generated' => [
            'OperationIdController',
        ];

        yield 'operationId generation conditionally_prepend' => [
            'OperationIdController',
            'OperationIdController.conditionally_prepend',
            [],
            [
                'nelmio_api_doc' => [
                    'operation_id_generation' => OperationIdGeneration::CONDITIONALLY_PREPEND,
                ],
            ],
        ];

        yield 'operationId generation conditionally_prepend string' => [
            'OperationIdController',
            'OperationIdController.conditionally_prepend',
            [],
            [
                'nelmio_api_doc' => [
                    'operation_id_generation' => OperationIdGeneration::CONDITIONALLY_PREPEND->value,
                ],
            ],
        ];

        yield 'operationId generation no_prepend' => [
            'OperationIdController',
            'OperationIdController.no_prepend',
            [],
            [
                'nelmio_api_doc' => [
                    'operation_id_generation' => OperationIdGeneration::NO_PREPEND,
                ],
            ],
        ];

        yield 'operationId generation no_prepend string' => [
            'OperationIdController',
            'OperationIdController.no_prepend',
            [],
            [
                'nelmio_api_doc' => [
                    'operation_id_generation' => OperationIdGeneration::NO_PREPEND->value,
                ],
            ],
        ];

        yield 'Symfony 6.3 MapQueryParameter attribute' => [
            'MapQueryParameterController',
        ];

        yield 'Symfony 6.3 MapRequestPayload attribute' => [
            'MapRequestPayloadController',
            null,
            [],
            [
                // Enable serializer
                'framework' => [
                    'property_info' => [
                        'enabled' => true,
                    ],
                    'serializer' => [
                        'enabled' => true,
                        'enable_attributes' => true,
                    ],
                    'validation' => [
                        'enabled' => true,
                        'enable_attributes' => true,
                        'static_method' => [
                            'loadValidatorMetadata',
                        ],
                        'translation_domain' => 'validators',
                        'email_validation_mode' => 'html5',
                        'mapping' => [
                            'paths' => [],
                        ],
                        'not_compromised_password' => [
                            'enabled' => true,
                            'endpoint' => null,
                        ],
                        'auto_mapping' => [],
                    ],
                ],
            ],
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
            [
                'nelmio_api_doc' => [
                    'documentation' => [
                        'info' => [
                            'title' => 'Test API',
                            'description' => 'Test API description',
                            'x-vendor' => [
                                'test' => 'Test vendor extension',
                            ],
                            'x-build' => '#SomeCommitHash',
                        ],
                        'components' => [
                            'schemas' => [
                                'Test' => [
                                    'type' => 'string',
                                    'x-vendor' => [
                                        'test' => 'Test vendor extension inside schema',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                StubProcessor::class => (new Definition(StubProcessor::class))
                    ->addTag('nelmio_api_doc.swagger.processor', ['priority' => -100, 'before' => CleanUnusedComponents::class]),
            ],
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
}
