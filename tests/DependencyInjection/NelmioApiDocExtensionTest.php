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

use Nelmio\ApiDocBundle\DependencyInjection\NelmioApiDocExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class NelmioApiDocExtensionTest extends TestCase
{
    public function testNameAliasesArePassedToModelRegistry(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', []);
        $extension = new NelmioApiDocExtension();
        $extension->load([[
            'areas' => [
                'default' => ['path_patterns' => ['/foo']],
                'commercial' => ['path_patterns' => ['/internal']],
            ],
            'models' => [
                'names' => [
                    [ // Test1 alias for all the areas
                        'alias' => 'Test1',
                        'type' => 'App\Test',
                    ],
                    [ // Foo1 alias for all the areas
                        'alias' => 'Foo1',
                        'type' => 'App\Foo',
                    ],
                    [ // overwrite Foo1 alias for all the commercial area
                        'alias' => 'Foo1',
                        'type' => 'App\Bar',
                        'areas' => ['commercial'],
                    ],
                ],
            ],
        ]], $container);

        $methodCalls = $container->getDefinition('nelmio_api_doc.generator.default')->getMethodCalls();
        $foundMethodCall = false;
        foreach ($methodCalls as $methodCall) {
            if ('setAlternativeNames' === $methodCall[0]) {
                self::assertEquals([
                    'Foo1' => [
                        'type' => 'App\\Foo',
                        'groups' => null,
                    ],
                    'Test1' => [
                        'type' => 'App\\Test',
                        'groups' => null,
                    ],
                ], $methodCall[1][0]);
                $foundMethodCall = true;
            }
        }
        self::assertTrue($foundMethodCall);

        $methodCalls = $container->getDefinition('nelmio_api_doc.generator.commercial')->getMethodCalls();
        $foundMethodCall = false;
        foreach ($methodCalls as $methodCall) {
            if ('setAlternativeNames' === $methodCall[0]) {
                self::assertEquals([
                    'Foo1' => [
                        'type' => 'App\\Bar',
                        'groups' => null,
                    ],
                    'Test1' => [
                        'type' => 'App\\Test',
                        'groups' => null,
                    ],
                ], $methodCall[1][0]);
                $foundMethodCall = true;
            }
        }
        self::assertTrue($foundMethodCall);
    }

    public function testMergesRootKeysFromMultipleConfigurations(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', []);
        $extension = new NelmioApiDocExtension();
        $extension->load([
            [
                'documentation' => [
                    'info' => [
                        'title' => 'API documentation',
                        'description' => 'This is the api documentation, use it wisely',
                    ],
                ],
            ],
            [
                'documentation' => [
                    'tags' => [
                        [
                            'name' => 'secured',
                            'description' => 'Requires authentication',
                        ],
                        [
                            'name' => 'another',
                            'description' => 'Another tag serving another purpose',
                        ],
                    ],
                ],
            ],
            [
                'documentation' => [
                    'paths' => [
                        '/api/v1/model' => [
                            'get' => [
                                'tags' => ['secured'],
                            ],
                        ],
                    ],
                ],
            ],
        ], $container);

        self::assertSame([
            'info' => [
                'title' => 'API documentation',
                'description' => 'This is the api documentation, use it wisely',
            ],
            'tags' => [
                [
                    'name' => 'secured',
                    'description' => 'Requires authentication',
                ],
                [
                    'name' => 'another',
                    'description' => 'Another tag serving another purpose',
                ],
            ],
            'paths' => [
                '/api/v1/model' => [
                    'get' => [
                        'tags' => ['secured'],
                    ],
                ],
            ],
        ], $container->getDefinition('nelmio_api_doc.describers.config')->getArgument(0));
    }

    /**
     * @dataProvider provideCacheConfig
     *
     * @param array<string, mixed> $config
     * @param array<string, mixed> $expectedValues
     */
    public function testApiDocGeneratorWithCachePool(array $config, array $expectedValues): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', []);

        $extension = new NelmioApiDocExtension();
        $extension->load([$config], $container);

        $reference = $container->getDefinition('nelmio_api_doc.generator.default')->getArgument(2);
        if (null === $expectedValues['defaultCachePool']) {
            self::assertNull($reference);
        } else {
            self::assertInstanceOf(Reference::class, $reference);
            self::assertSame($expectedValues['defaultCachePool'], (string) $reference);
        }

        $reference = $container->getDefinition('nelmio_api_doc.generator.area1')->getArgument(2);
        if (null === $expectedValues['area1CachePool']) {
            self::assertNull($reference);
        } else {
            self::assertInstanceOf(Reference::class, $reference);
            self::assertSame($expectedValues['area1CachePool'], (string) $reference);
        }

        $cacheItemId = $container->getDefinition('nelmio_api_doc.generator.default')->getArgument(3);
        self::assertSame($expectedValues['defaultCacheItemId'], $cacheItemId);

        $cacheItemId = $container->getDefinition('nelmio_api_doc.generator.area1')->getArgument(3);
        self::assertSame($expectedValues['area1CacheItemId'], $cacheItemId);
    }

    public static function provideCacheConfig(): \Generator
    {
        yield 'default cache.item_id & area appending' => [
            'config' => [
                'cache' => [
                    'pool' => 'test.cache',
                ],
                'areas' => [
                    'default' => [],
                    'area1' => [],
                ],
            ],
            'expectedValues' => [
                'defaultCachePool' => 'test.cache',
                'defaultCacheItemId' => 'openapi_doc.default',
                'area1CachePool' => 'test.cache',
                'area1CacheItemId' => 'openapi_doc.area1',
            ],
        ];

        yield 'configuring cache.item_id & area appending' => [
            'config' => [
                'cache' => [
                    'pool' => 'test.cache',
                    'item_id' => 'test.docs',
                ],
                'areas' => [
                    'default' => [],
                    'area1' => [],
                ],
            ],
            'expectedValues' => [
                'defaultCachePool' => 'test.cache',
                'defaultCacheItemId' => 'test.docs.default',
                'area1CachePool' => 'test.cache',
                'area1CacheItemId' => 'test.docs.area1',
            ],
        ];

        yield 'overwriting item_id for an area' => [
            'config' => [
                'cache' => [
                    'pool' => 'test.cache',
                    'item_id' => 'nelmio.docs',
                ],
                'areas' => [
                    'default' => [
                        'cache' => [
                            'pool' => 'test.cache.default',
                            'item_id' => 'nelmio.docs.default',
                        ],
                    ],
                    'area1' => [],
                ],
            ],
            'expectedValues' => [
                'defaultCachePool' => 'test.cache.default',
                'defaultCacheItemId' => 'nelmio.docs.default',
                'area1CachePool' => 'test.cache',
                'area1CacheItemId' => 'nelmio.docs.area1',
            ],
        ];

        yield 'setting cache for a single area' => [
            'config' => [
                'areas' => [
                    'default' => [
                    ],
                    'area1' => [
                        'cache' => [
                            'pool' => 'app.cache',
                            'item_id' => 'docs',
                        ],
                    ],
                ],
            ],
            'expectedValues' => [
                'defaultCachePool' => null,
                'defaultCacheItemId' => 'openapi_doc.default',
                'area1CachePool' => 'app.cache',
                'area1CacheItemId' => 'docs',
            ],
        ];

        yield 'setting a global cache pool & shared item_id' => [
            'config' => [
                'cache' => [
                    'pool' => 'app.cache',
                ],
                'areas' => [
                    'default' => [
                        'cache' => [
                            'item_id' => 'docs',
                        ],
                    ],
                    'area1' => [
                        'cache' => [
                            'item_id' => 'docs',
                        ],
                    ],
                ],
            ],
            'expectedValues' => [
                'defaultCachePool' => 'app.cache',
                'defaultCacheItemId' => 'docs',
                'area1CachePool' => 'app.cache',
                'area1CacheItemId' => 'docs',
            ],
        ];
    }
}
