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
use Nelmio\ApiDocBundle\Tests\Functional\Controller\SecuredApiController;
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
    public function testControllers(?string $controller, ?string $fixtureSuffix = null, array $extraBundles = [], array $extraConfigs = [], array $extraDefinitions = [], ?\Closure $extraRoutes = null): void
    {
        $fixtureName = null !== $fixtureSuffix ? $controller.'.'.$fixtureSuffix : $controller;

        $routingConfiguration = function (RoutingConfigurator &$routes) use ($controller, $extraRoutes) {
            if (null !== $extraRoutes) {
                ($extraRoutes)($routes);
            }

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
            'defaults',
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
            null,
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
            'cleanup-components',
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
            'conditionally_prepend',
            [],
            [
                'nelmio_api_doc' => [
                    'operation_id_generation' => OperationIdGeneration::CONDITIONALLY_PREPEND,
                ],
            ],
        ];

        yield 'operationId generation conditionally_prepend string' => [
            'OperationIdController',
            'conditionally_prepend',
            [],
            [
                'nelmio_api_doc' => [
                    'operation_id_generation' => OperationIdGeneration::CONDITIONALLY_PREPEND->value,
                ],
            ],
        ];

        yield 'operationId generation no_prepend' => [
            'OperationIdController',
            'no_prepend',
            [],
            [
                'nelmio_api_doc' => [
                    'operation_id_generation' => OperationIdGeneration::NO_PREPEND,
                ],
            ],
        ];

        yield 'operationId generation no_prepend string' => [
            'OperationIdController',
            'no_prepend',
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

        yield 'Name collision with groups' => [
            'ApiController81Collisions',
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

        yield 'Security documentation API key' => [
            'SecuredApiController',
            'api-key',
            [],
            [
                'nelmio_api_doc' => [
                    'areas' => [
                        'default' => [
                            'security' => [
                                'ApiKeyAuth' => [
                                    'type' => 'apiKey',
                                    'name' => 'X-API-KEY',
                                    'in' => 'header',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'Security documentation basic auth' => [
            'SecuredApiController',
            'basic-auth',
            [],
            [
                'nelmio_api_doc' => [
                    'areas' => [
                        'default' => [
                            'security' => [
                                'BasicAuth' => [
                                    'type' => 'http',
                                    'scheme' => 'basic',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'Security documentation bearer auth' => [
            'SecuredApiController',
            'bearer-auth',
            [],
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

        yield 'Security documentation OAuth2' => [
            'SecuredApiController',
            'oauth2',
            [],
            [
                'nelmio_api_doc' => [
                    'areas' => [
                        'default' => [
                            'security' => [
                                'OAuth2' => [
                                    'type' => 'oauth2',
                                    'description' => 'This API uses OAuth 2 with the implicit grant flow. [More info](https://api.example.com/docs/auth)',
                                    'flows' => [
                                        'implicit' => [
                                            'authorizationUrl' => 'https://api.example.com/oauth/authorize',
                                            'scopes' => [
                                                'read:messages' => 'Read messages',
                                                'write:messages' => 'Write messages',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'Security documentation OAuth2 multiple flows' => [
            'SecuredApiController',
            'oauth2-multiple-flows',
            [],
            [
                'nelmio_api_doc' => [
                    'areas' => [
                        'default' => [
                            'security' => [
                                'oAuthSample' => [
                                    'type' => 'oauth2',
                                    'description' => 'This API uses OAuth 2 with the implicit grant flow. [More info](https://api.example.com/docs/auth)',
                                    'flows' => [
                                        'authorizationCode' => [
                                            'authorizationUrl' => 'https://api.example.com/oauth/authorize',
                                            'tokenUrl' => 'https://api.example.com/oauth/token',
                                            'scopes' => [
                                                'read:messages' => 'Read messages',
                                                'write:messages' => 'Write messages',
                                            ],
                                        ],
                                        'implicit' => [
                                            'authorizationUrl' => 'https://api.example.com/oauth/authorize',
                                            'scopes' => [
                                                'read:messages' => 'Read messages',
                                                'write:messages' => 'Write messages',
                                            ],
                                        ],
                                        'password' => [
                                            'tokenUrl' => 'https://api.example.com/oauth/token',
                                            'scopes' => [
                                                'read:messages' => 'Read messages',
                                                'write:messages' => 'Write messages',
                                            ],
                                        ],
                                        'clientCredentials' => [
                                            'tokenUrl' => 'https://api.example.com/oauth/token',
                                            'scopes' => [
                                                'read:messages' => 'Read messages',
                                                'write:messages' => 'Write messages',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'Security documentation OpenId Connect' => [
            'SecuredApiController',
            'openid-connect',
            [],
            [
                'nelmio_api_doc' => [
                    'areas' => [
                        'default' => [
                            'security' => [
                                'OpenIdConnect' => [
                                    'type' => 'openIdConnect',
                                    'openIdConnectUrl' => 'https://api.example.com/.well-known/openid-configuration',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'Security documentation cookie' => [
            'SecuredApiController',
            'cookie',
            [],
            [
                'nelmio_api_doc' => [
                    'areas' => [
                        'default' => [
                            'security' => [
                                'CookieAuth' => [
                                    'type' => 'apiKey',
                                    'name' => 'sessionId',
                                    'in' => 'cookie',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'Security documentation' => [
            'InvokableController',
            'insecured',
            [],
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

        yield 'Security documentation for manually registered controller' => [
            null,
            'security-manually-registered',
            [],
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
            [],
            function (RoutingConfigurator $routes) {
                $routes->add('security-manually-registered', '/security-manually-registered')
                    ->controller([SecuredApiController::class, 'fetchArticleAction'])
                    ->methods(['GET']);
            },
        ];

        yield 'Security documentation without controllers does not throw' => [
            null,
            'no-controllers-registered',
            [],
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

        yield 'Tag from class on inherited controller' => [
            'InvoiceDocumentController',
        ];

        yield 'Controller as service' => [
            null,
            'controller-as-service',
            [],
            [],
            [
                'web_custom_controller' => (new Definition(SecuredApiController::class))
                ->setPublic(true),
            ],
            function (RoutingConfigurator $routes) {
                $routes->add('route_name', '/')
                    ->controller('web_custom_controller::fetchArticleAction')
                    ->methods(['GET']);
            },
        ];

        yield 'Controller as service with security' => [
            null,
            'controller-as-service-with-security',
            [],
            [
                'nelmio_api_doc' => [
                    'areas' => [
                        'default' => [
                            'security' => [
                                'BasicAuth' => [
                                    'type' => 'http',
                                    'scheme' => 'basic',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'web_custom_controller' => (new Definition(SecuredApiController::class))
                    ->setPublic(true),
            ],
            function (RoutingConfigurator $routes) {
                $routes->add('route_name', '/')
                    ->controller('web_custom_controller::fetchArticleAction')
                    ->methods(['GET']);
            },
        ];

        if (version_compare(Kernel::VERSION, '7.2.0', '>=')) {
            yield 'Generic types' => [
                'GenericTypesController',
                null,
                [],
                [
                    'nelmio_api_doc' => [
                        'type_info' => true,
                    ],
                ],
            ];
        }
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
