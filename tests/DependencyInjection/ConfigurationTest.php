<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\DependencyInjection;

use Nelmio\ApiDocBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    private Processor $processor;

    protected function setUp(): void
    {
        $this->processor = new Processor();

        parent::setUp();
    }

    public function testDefaultArea(): void
    {
        $config = $this->processor->processConfiguration(new Configuration(), [['areas' => ['path_patterns' => ['/foo']]]]);

        self::assertSame(
            [
                'default' => [
                    'path_patterns' => ['/foo'],
                    'host_patterns' => [],
                    'name_patterns' => [],
                    'security' => [],
                    'with_attribute' => false,
                    'disable_default_routes' => false,
                    'documentation' => [],
                ],
            ],
            $config['areas']
        );
    }

    public function testAreas(): void
    {
        $config = $this->processor->processConfiguration(new Configuration(), [['areas' => $areas = [
            'default' => [
                'path_patterns' => ['/foo'],
                'host_patterns' => [],
                'security' => [],
                'with_attribute' => false,
                'documentation' => [],
                'name_patterns' => [],
                'disable_default_routes' => false,
            ],
            'internal' => [
                'path_patterns' => ['/internal'],
                'host_patterns' => ['^swagger\.'],
                'security' => [],
                'with_attribute' => false,
                'documentation' => [],
                'name_patterns' => [],
                'disable_default_routes' => false,
            ],
            'commercial' => [
                'path_patterns' => ['/internal'],
                'host_patterns' => [],
                'security' => [],
                'with_attribute' => false,
                'documentation' => [],
                'name_patterns' => [],
                'disable_default_routes' => false,
            ],
            'secured' => [
                'path_patterns' => ['/secured'],
                'host_patterns' => [],
                'security' => [
                    'basic' => [
                        'type' => 'http',
                        'scheme' => 'basic',
                    ],
                ],
                'with_attribute' => false,
                'documentation' => [],
                'name_patterns' => [],
                'disable_default_routes' => false,
            ],
        ]]]);

        self::assertSame($areas, $config['areas']);
    }

    public function testAlternativeNames(): void
    {
        $config = $this->processor->processConfiguration(new Configuration(), [[
            'models' => [
                'names' => [
                    [
                        'alias' => 'Foo1',
                        'type' => 'App\Foo',
                        'groups' => ['group'],
                    ],
                    [
                        'alias' => 'Foo2',
                        'type' => 'App\Foo',
                        'groups' => [],
                    ],
                    [
                        'alias' => 'Foo3',
                        'type' => 'App\Foo',
                    ],
                    [
                        'alias' => 'Foo4',
                        'type' => 'App\Foo',
                        'groups' => ['group'],
                        'areas' => ['internal'],
                    ],
                    [
                        'alias' => 'Foo1',
                        'type' => 'App\Foo',
                        'areas' => ['internal'],
                    ],
                    [
                        'alias' => 'Foo1',
                        'type' => 'App\Foo',
                        'groups' => ['group1', ['group2', 'parent' => 'child3']],
                    ],
                    [
                        'alias' => 'Foo1',
                        'type' => 'App\Foo',
                        'options' => null,
                    ],
                    [
                        'alias' => 'Foo1',
                        'type' => 'App\Foo',
                        'options' => ['foo' => 'bar'],
                    ],
                    [
                        'alias' => 'Foo1',
                        'type' => 'App\Foo',
                        'serializationContext' => ['useJms' => false, 'foo' => 'bar'],
                    ],
                ],
            ],
        ]]);
        self::assertEquals([
            [
                'alias' => 'Foo1',
                'type' => 'App\Foo',
                'groups' => ['group'],
                'options' => null,
                'serializationContext' => [],
                'areas' => [],
            ],
            [
                'alias' => 'Foo2',
                'type' => 'App\Foo',
                'groups' => [],
                'options' => null,
                'serializationContext' => [],
                'areas' => [],
            ],
            [
                'alias' => 'Foo3',
                'type' => 'App\Foo',
                'groups' => null,
                'options' => null,
                'serializationContext' => [],
                'areas' => [],
            ],
            [
                'alias' => 'Foo4',
                'type' => 'App\\Foo',
                'groups' => ['group'],
                'options' => null,
                'serializationContext' => [],
                'areas' => ['internal'],
            ],
            [
                'alias' => 'Foo1',
                'type' => 'App\\Foo',
                'groups' => null,
                'options' => null,
                'serializationContext' => [],
                'areas' => ['internal'],
            ],
            [
                'alias' => 'Foo1',
                'type' => 'App\Foo',
                'groups' => ['group1', ['group2', 'parent' => 'child3']],
                'options' => null,
                'serializationContext' => [],
                'areas' => [],
            ],
            [
                'alias' => 'Foo1',
                'type' => 'App\Foo',
                'groups' => null,
                'options' => null,
                'serializationContext' => [],
                'areas' => [],
            ],
            [
                'alias' => 'Foo1',
                'type' => 'App\Foo',
                'groups' => null,
                'options' => ['foo' => 'bar'],
                'serializationContext' => [],
                'areas' => [],
            ],
            [
                'alias' => 'Foo1',
                'type' => 'App\Foo',
                'groups' => null,
                'options' => null,
                'serializationContext' => [
                    'useJms' => false,
                    'foo' => 'bar',
                ],
                'areas' => [],
            ],
        ], $config['models']['names']);
    }

    /**
     * @param mixed[] $configuration
     */
    #[DataProvider('provideInvalidConfiguration')]
    public function testInvalidConfiguration(array $configuration, string $expectedError): void
    {
        self::expectException(InvalidConfigurationException::class);
        self::expectExceptionMessage($expectedError);

        $this->processor->processConfiguration(new Configuration(), [$configuration]);
    }

    public static function provideInvalidConfiguration(): \Generator
    {
        yield 'invalid html_config.assets_mode' => [
            [
                'html_config' => [
                    'assets_mode' => 'invalid',
                ],
            ],
            'Invalid assets mode "invalid"',
        ];

        yield 'do not set cache.item_id' => [
            [
                'cache' => [
                    'pool' => null,
                    'item_id' => 'some-id',
                ],
            ],
            'Can not set cache.item_id if cache.pool is null',
        ];

        yield 'do not set cache.item_id, default pool' => [
            [
                'cache' => [
                    'item_id' => 'some-id',
                ],
            ],
            'Can not set cache.item_id if cache.pool is null',
        ];

        yield 'default area missing ' => [
            [
                'areas' => [
                    'some_not_default_area' => [],
                ],
            ],
            'You must specify a `default` area under `nelmio_api_doc.areas`.',
        ];

        yield 'invalid groups value for model ' => [
            [
                'models' => [
                    'names' => [
                        [
                            'alias' => 'Foo1',
                            'type' => 'App\Foo',
                            'groups' => 'invalid_string_value',
                        ],
                    ],
                ],
            ],
            'Model groups must be either `null` or an array.',
        ];

        yield 'invalid options value for model' => [
            [
                'models' => [
                    'names' => [
                        [
                            'alias' => 'Foo1',
                            'type' => 'App\Foo',
                            'options' => 'invalid_string_value',
                        ],
                    ],
                ],
            ],
            'Model options must be either `null` or an array.',
        ];

        yield 'invalid security schema `type`' => [
            [
                'areas' => [
                    'default' => [
                        'security' => [
                            'invalid' => [
                                'type' => 'SomeInvalidType',
                            ],
                        ],
                    ],
                ],
            ],
            'Invalid configuration for path "nelmio_api_doc.areas.default.security.invalid.type": Invalid `type` value "SomeInvalidType". Available types are: http, apiKey, openIdConnect, oauth2',
        ];

        yield 'invalid security schema `scheme`' => [
            [
                'areas' => [
                    'default' => [
                        'security' => [
                            'basicAuth' => [
                                'scheme' => 'SomeInvalidScheme',
                            ],
                        ],
                    ],
                ],
            ],
            'nelmio_api_doc.areas.default.security.basicAuth.scheme": Invalid `scheme` value "SomeInvalidScheme". Available schemes are: basic, bearer',
        ];

        yield 'invalid security schema `in`' => [
            [
                'areas' => [
                    'default' => [
                        'security' => [
                            'basicAuth' => [
                                'in' => 'SomeInvalidIn',
                            ],
                        ],
                    ],
                ],
            ],
            'Invalid configuration for path "nelmio_api_doc.areas.default.security.basicAuth.in": Invalid `in` value "SomeInvalidIn". Available locations are: header, query, cookie',
        ];
    }
}
