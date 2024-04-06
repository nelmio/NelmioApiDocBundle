<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\SwaggerPhp;

use Exception;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;

/**
 * Class UtilTest.
 *
 * @covers \Nelmio\ApiDocBundle\OpenApiPhp\Util::getOperation
 * @covers \Nelmio\ApiDocBundle\OpenApiPhp\Util::getOperationParameter
 * @covers \Nelmio\ApiDocBundle\OpenApiPhp\Util::getChild
 * @covers \Nelmio\ApiDocBundle\OpenApiPhp\Util::getCollectionItem
 * @covers \Nelmio\ApiDocBundle\OpenApiPhp\Util::getIndexedCollectionItem
 * @covers \Nelmio\ApiDocBundle\OpenApiPhp\Util::searchCollectionItem
 * @covers \Nelmio\ApiDocBundle\OpenApiPhp\Util::searchIndexedCollectionItem
 * @covers \Nelmio\ApiDocBundle\OpenApiPhp\Util::createCollectionItem
 * @covers \Nelmio\ApiDocBundle\OpenApiPhp\Util::createChild
 * @covers \Nelmio\ApiDocBundle\OpenApiPhp\Util::createContext
 * @covers \Nelmio\ApiDocBundle\OpenApiPhp\Util::merge
 * @covers \Nelmio\ApiDocBundle\OpenApiPhp\Util::mergeFromArray
 * @covers \Nelmio\ApiDocBundle\OpenApiPhp\Util::mergeChild
 * @covers \Nelmio\ApiDocBundle\OpenApiPhp\Util::mergeCollection
 * @covers \Nelmio\ApiDocBundle\OpenApiPhp\Util::mergeTyped
 * @covers \Nelmio\ApiDocBundle\OpenApiPhp\Util::mergeProperty
 * @covers \Nelmio\ApiDocBundle\OpenApiPhp\Util::getNestingIndexes
 */
class UtilTest extends TestCase
{
    private Context $rootContext;

    private OA\OpenApi $rootAnnotation;

    public function setUp(): void
    {
        parent::setUp();

        $this->rootContext = new Context(['isTestingRoot' => true]);
        $this->rootAnnotation = self::createObj(OA\OpenApi::class, ['_context' => $this->rootContext]);

        set_error_handler(
            static function ($errno, $errstr) {
                throw new \Exception($errstr, $errno);
            },
            E_ALL
        );
    }

    public function tearDown(): void
    {
        restore_error_handler();
    }

    public function testCreateContextSetsParentContext(): void
    {
        $context = Util::createContext([], $this->rootContext);

        $this->assertContextIsConnectedToRootContext($context);
    }

    public function testCreateContextWithProperties(): void
    {
        $context = Util::createContext(['testing' => 'trait']);

        self::assertTrue($context->is('testing'));
        self::assertSame('trait', $context->testing);
    }

    public function testCreateChild(): void
    {
        $info = Util::createChild($this->rootAnnotation, OA\Info::class);

        self::assertInstanceOf(OA\Info::class, $info);
    }

    public function testCreateChildHasContext(): void
    {
        $info = Util::createChild($this->rootAnnotation, OA\Info::class);

        self::assertInstanceOf(Context::class, $info->_context);
    }

    public function testCreateChildHasNestedContext(): void
    {
        $path = Util::createChild($this->rootAnnotation, OA\PathItem::class);
        $this->assertIsNested($this->rootAnnotation, $path);

        $parameter = Util::createChild($path, OA\Parameter::class);
        $this->assertIsNested($path, $parameter);

        $schema = Util::createChild($parameter, OA\Schema::class);
        $this->assertIsNested($parameter, $schema);

        $this->assertIsConnectedToRootContext($schema);
    }

    public function testCreateChildWithEmptyProperties(): void
    {
        $properties = [];
        /** @var OA\Info $info */
        $info = Util::createChild($this->rootAnnotation, OA\Info::class, $properties);

        $properties = array_filter(\get_object_vars($info), function ($key) {
            return 0 !== \strpos($key, '_');
        }, ARRAY_FILTER_USE_KEY);

        self::assertEquals([Generator::UNDEFINED], array_unique(array_values($properties)));

        $this->assertIsNested($this->rootAnnotation, $info);
        $this->assertIsConnectedToRootContext($info);
    }

    public function testCreateChildWithProperties(): void
    {
        $properties = ['title' => 'testing', 'version' => '999', 'x' => new \stdClass()];
        /** @var OA\Info $info */
        $info = Util::createChild($this->rootAnnotation, OA\Info::class, $properties);

        self::assertSame($info->title, $properties['title']);
        self::assertSame($info->version, $properties['version']);
        self::assertSame($info->x, $properties['x']);

        $this->assertIsNested($this->rootAnnotation, $info);
        $this->assertIsConnectedToRootContext($info);
    }

    public function testCreateCollectionItemAddsCreatedItemToCollection(): void
    {
        $collection = 'paths';
        $class = OA\PathItem::class;

        $p1 = Util::createCollectionItem($this->rootAnnotation, $collection, $class);
        self::assertSame(0, $p1);
        self::assertCount(1, $this->rootAnnotation->{$collection});
        self::assertInstanceOf($class, $this->rootAnnotation->{$collection}[$p1]);
        $this->assertIsNested($this->rootAnnotation, $this->rootAnnotation->{$collection}[$p1]);
        $this->assertIsConnectedToRootContext($this->rootAnnotation->{$collection}[$p1]);

        $p2 = Util::createCollectionItem($this->rootAnnotation, $collection, $class);
        self::assertSame(1, $p2);
        self::assertCount(2, $this->rootAnnotation->{$collection});
        self::assertInstanceOf($class, $this->rootAnnotation->{$collection}[$p2]);
        $this->assertIsNested($this->rootAnnotation, $this->rootAnnotation->{$collection}[$p2]);
        $this->assertIsConnectedToRootContext($this->rootAnnotation->{$collection}[$p2]);

        $this->rootAnnotation->components = Util::createChild($this->rootAnnotation, OA\Components::class);

        $collection = 'schemas';
        $class = OA\Schema::class;

        $d1 = Util::createCollectionItem($this->rootAnnotation->components, $collection, $class);
        self::assertSame(0, $d1);
        self::assertCount(1, $this->rootAnnotation->components->{$collection});
        self::assertInstanceOf($class, $this->rootAnnotation->components->{$collection}[$d1]);
        $this->assertIsNested($this->rootAnnotation->components, $this->rootAnnotation->components->{$collection}[$d1]);
        $this->assertIsConnectedToRootContext($this->rootAnnotation->components->{$collection}[$d1]);
    }

    public function testCreateCollectionItemDoesNotAddToUnknownProperty(): void
    {
        $collection = 'foobars';
        $class = OA\Info::class;

        self::expectException(\Exception::class);
        self::expectExceptionMessage("Property \"foobars\" doesn't exist");
        Util::createCollectionItem($this->rootAnnotation, $collection, $class);

        self::expectException(\Exception::class);
        self::expectExceptionMessage("Property \"foobars\" doesn't exist");
        self::assertNull($this->rootAnnotation->{$collection}); /* @phpstan-ignore-line */
    }

    public function testSearchCollectionItem(): void
    {
        $item1 = new \stdClass();
        $item1->prop1 = 'item 1 prop 1';
        $item1->prop2 = 'item 1 prop 2';
        $item1->prop3 = 'item 1 prop 3';

        $item2 = new \stdClass();
        $item2->prop1 = 'item 2 prop 1';
        $item2->prop2 = 'item 2 prop 2';
        $item2->prop3 = 'item 2 prop 3';

        $collection = [
            $item1,
            $item2,
        ];

        self::assertSame(0, Util::searchCollectionItem($collection, \get_object_vars($item1)));
        self::assertSame(1, Util::searchCollectionItem($collection, \get_object_vars($item2)));

        self::assertNull(Util::searchCollectionItem(
            $collection,
            array_merge(\get_object_vars($item2), ['prop3' => 'foobar'])
        ));

        $search = ['baz' => 'foobar'];

        self::expectException(\Exception::class);
        self::expectExceptionMessage('Undefined property: stdClass::$baz');
        Util::searchCollectionItem($collection, array_merge(\get_object_vars($item2), $search));

        // no exception on empty collection
        self::assertNull(Util::searchCollectionItem([], \get_object_vars($item2)));
    }

    /**
     * @dataProvider provideIndexedCollectionData
     *
     * @param array<mixed> $setup
     * @param array<mixed> $asserts
     */
    public function testSearchIndexedCollectionItem(array $setup, array $asserts): void
    {
        foreach ($asserts as $collection => $items) {
            foreach ($items as $assert) {
                $setupCollection = !isset($assert['components']) ?
                    ($setup[$collection] ?? []) :
                    (Generator::UNDEFINED !== $setup['components']->{$collection} ? $setup['components']->{$collection} : []);

                // get the indexing correct within haystack preparation
                $properties = array_fill(0, \count($setupCollection), null);

                // prepare the haystack array
                foreach ($items as $assertItem) {
                    // e.g. $properties[1] = self::createObj(OA\PathItem::class, ['path' => 'path 1'])
                    $properties[$assertItem['index']] = self::createObj($assertItem['class'], [
                        $assertItem['key'] => $assertItem['value'],
                    ]);
                }

                self::assertSame(
                    $assert['index'],
                    Util::searchIndexedCollectionItem($properties, $assert['key'], $assert['value']),
                    sprintf('Failed to get the correct index for %s', print_r($assert, true))
                );
            }
        }
    }

    /**
     * @dataProvider provideIndexedCollectionData
     *
     * @param array<mixed> $setup
     * @param array<mixed> $asserts
     */
    public function testGetIndexedCollectionItem(array $setup, array $asserts): void
    {
        $parent = new $setup['class'](array_merge(
            $this->getSetupPropertiesWithoutClass($setup),
            ['_context' => $this->rootContext]
        ));

        foreach ($asserts as $collection => $items) {
            foreach ($items as $assert) {
                $itemParent = !isset($assert['components']) ? $parent : $parent->components;

                self::assertTrue(is_a($assert['class'], OA\AbstractAnnotation::class, true), sprintf('Invalid class %s', $assert['class']));
                $child = Util::getIndexedCollectionItem(
                    $itemParent,
                    $assert['class'],
                    $assert['value']
                );

                self::assertInstanceOf($assert['class'], $child);
                self::assertSame($child->{$assert['key']}, $assert['value']);
                self::assertSame(
                    $itemParent->{$collection}[$assert['index']],
                    $child
                );

                $setupHaystack = !isset($assert['components']) ?
                    $setup[$collection] ?? [] :
                    $setup['components']->{$collection} ?? [];

                // the children created within provider are not connected
                if (!\in_array($child, $setupHaystack, true)) {
                    $this->assertIsNested($itemParent, $child);
                    $this->assertIsConnectedToRootContext($child);
                }
            }
        }
    }

    public static function provideIndexedCollectionData(): \Generator
    {
        yield [
            'setup' => [
                'class' => OA\OpenApi::class,
                'paths' => [
                    self::createObj(OA\PathItem::class, ['path' => 'path 0']),
                ],
                'components' => self::createObj(OA\Components::class, [
                    'parameters' => [
                        self::createObj(OA\Parameter::class, ['parameter' => 'parameter 0']),
                        self::createObj(OA\Parameter::class, ['parameter' => 'parameter 1']),
                    ],
                ]),
            ],
            'assert' => [
                // one fixed within setup and one dynamically created
                'paths' => [
                    [
                        'index' => 0,
                        'class' => OA\PathItem::class,
                        'key' => 'path',
                        'value' => 'path 0',
                    ],
                    [
                        'index' => 1,
                        'class' => OA\PathItem::class,
                        'key' => 'path',
                        'value' => 'path 1',
                    ],
                ],
                // search indexes out of order followed by dynamically created
                'parameters' => [
                    [
                        'index' => 1,
                        'class' => OA\Parameter::class,
                        'key' => 'parameter',
                        'value' => 'parameter 1',
                        'components' => true,
                    ],
                    [
                        'index' => 0,
                        'class' => OA\Parameter::class,
                        'key' => 'parameter',
                        'value' => 'parameter 0',
                        'components' => true,
                    ],
                    [
                        'index' => 2,
                        'class' => OA\Parameter::class,
                        'key' => 'parameter',
                        'value' => 'parameter 2',
                        'components' => true,
                    ],
                ],
                // two dynamically created
                'responses' => [
                    [
                        'index' => 0,
                        'class' => OA\Response::class,
                        'key' => 'response',
                        'value' => 'response 0',
                        'components' => true,
                    ],
                    [
                        'index' => 1,
                        'class' => OA\Response::class,
                        'key' => 'response',
                        'value' => 'response 1',
                        'components' => true,
                    ],
                ],
                // for sake of completeness
                'securitySchemes' => [
                    [
                        'index' => 0,
                        'class' => OA\SecurityScheme::class,
                        'key' => 'securityScheme',
                        'value' => 'securityScheme 0',
                        'components' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideChildData
     *
     * @param array<mixed> $setup
     * @param array<mixed> $asserts
     */
    public function testGetChild(array $setup, array $asserts): void
    {
        $parent = new $setup['class'](array_merge(
            $this->getSetupPropertiesWithoutClass($setup),
            ['_context' => $this->rootContext]
        ));

        foreach ($asserts as $key => $assert) {
            if (\array_key_exists('exceptionMessage', $assert)) {
                $this->expectExceptionMessage($assert['exceptionMessage']);
            }
            self::assertTrue(is_a($assert['class'], OA\AbstractAnnotation::class, true), sprintf('Invalid class %s', $assert['class']));
            $child = Util::getChild($parent, $assert['class'], $assert['props']);

            self::assertInstanceOf($assert['class'], $child);
            self::assertSame($child, $parent->{$key});

            if (\array_key_exists($key, $setup)) {
                self::assertSame($setup[$key], $parent->{$key});
            }

            self::assertEquals($assert['props'], $this->getNonDefaultProperties($child));
        }
    }

    public static function provideChildData(): \Generator
    {
        yield [
            'setup' => [
                'class' => OA\PathItem::class,
                'get' => self::createObj(OA\Get::class, []),
            ],
            'assert' => [
                // fixed within setup
                'get' => [
                    'class' => OA\Get::class,
                    'props' => [],
                ],
                // create new without props
                'put' => [
                    'class' => OA\Put::class,
                    'props' => [],
                ],
                // create new with multiple props
                'delete' => [
                    'class' => OA\Delete::class,
                    'props' => [
                        'summary' => 'testing delete',
                        'deprecated' => true,
                    ],
                ],
            ],
        ];

        yield [
            'setup' => [
                'class' => OA\Parameter::class,
            ],
            'assert' => [
                // create new with multiple props
                'schema' => [
                    'class' => OA\Schema::class,
                    'props' => [
                        'ref' => '#/testing/schema',
                        'minProperties' => 0,
                        'enum' => [null, 'check', 999, false],
                    ],
                ],
            ],
        ];

        yield [
            'setup' => [
                'class' => OA\Parameter::class,
            ],
            'assert' => [
                // externalDocs triggers invalid argument exception
                'schema' => [
                    'class' => OA\Schema::class,
                    'props' => [
                        'externalDocs' => [],
                    ],
                    'exceptionMessage' => 'Nesting Annotations is not supported.',
                ],
            ],
        ];
    }

    public function testGetOperationParameterReturnsExisting(): void
    {
        $name = 'operation name';
        $in = 'operation in';

        $parameter = self::createObj(OA\Parameter::class, ['name' => $name, 'in' => $in]);
        $operation = self::createObj(OA\Get::class, ['parameters' => [
            self::createObj(OA\Parameter::class, []),
            self::createObj(OA\Parameter::class, ['name' => 'foo']),
            self::createObj(OA\Parameter::class, ['in' => 'bar']),
            self::createObj(OA\Parameter::class, ['name' => $name, 'in' => 'bar']),
            self::createObj(OA\Parameter::class, ['name' => 'foo', 'in' => $in]),
            $parameter,
        ]]);

        $actual = Util::getOperationParameter($operation, $name, $in);
        self::assertSame($parameter, $actual);
    }

    public function testGetOperationParameterCreatesWithNameAndIn(): void
    {
        $name = 'operation name';
        $in = 'operation in';

        $operation = self::createObj(OA\Get::class, ['parameters' => [
            self::createObj(OA\Parameter::class, []),
            self::createObj(OA\Parameter::class, ['name' => 'foo']),
            self::createObj(OA\Parameter::class, ['in' => 'bar']),
            self::createObj(OA\Parameter::class, ['name' => $name, 'in' => 'bar']),
            self::createObj(OA\Parameter::class, ['name' => 'foo', 'in' => $in]),
        ]]);

        $actual = Util::getOperationParameter($operation, $name, $in);
        self::assertSame($name, $actual->name);
        self::assertSame($in, $actual->in);
    }

    public function testGetOperationReturnsExisting(): void
    {
        $get = self::createObj(OA\Get::class, []);
        $path = self::createObj(OA\PathItem::class, ['get' => $get]);

        self::assertSame($get, Util::getOperation($path, 'get'));
    }

    public function testGetOperationCreatesWithPath(): void
    {
        $pathStr = '/testing/get/path';
        $path = self::createObj(OA\PathItem::class, ['path' => $pathStr]);

        $get = Util::getOperation($path, 'get');
        self::assertInstanceOf(OA\Get::class, $get);
        self::assertSame($pathStr, $get->path);
    }

    public function testMergeWithEmptyArray(): void
    {
        $api = self::createObj(OA\OpenApi::class, ['_context' => new Context()]);
        $expected = json_encode($api);

        Util::merge($api, [], false);
        $actual = json_encode($api);

        self::assertSame($expected, $actual);

        Util::merge($api, [], true);
        $actual = json_encode($api);

        self::assertSame($expected, $actual);
    }

    /**
     * @dataProvider provideMergeData
     *
     * @param array<mixed>              $setup
     * @param array<mixed>|\ArrayObject $merge
     * @param array<mixed>              $assert
     */
    public function testMerge(array $setup, $merge, array $assert): void
    {
        $api = self::createObj(OA\OpenApi::class, $setup + ['_context' => new Context()]);

        Util::merge($api, $merge, false);
        self::assertTrue($api->validate());
        $actual = json_decode(json_encode($api), true);

        self::assertEquals($assert, $actual);
    }

    public static function provideMergeData(): \Generator
    {
        $no = 'do not overwrite';
        $yes = 'do overwrite';

        $requiredInfo = ['title' => '', 'version' => ''];

        $setupDefaults = [
            'info' => self::createObj(OA\Info::class, $requiredInfo),
            'paths' => [],
        ];
        $assertDefaults = [
            'info' => $requiredInfo,
            'openapi' => '3.0.0',
            'paths' => [],
        ];

        yield [
            // simple child merge
            'setup' => [
                'info' => self::createObj(OA\Info::class, ['version' => $no]),
                'paths' => [],
            ],
            'merge' => [
                'info' => ['title' => $yes, 'version' => $yes],
            ],
            'assert' => [
                'info' => ['title' => $yes, 'version' => $no],
            ] + $assertDefaults,
        ];

        yield [
            // Parse server url with variables, see https://github.com/nelmio/NelmioApiDocBundle/issues/1691
            'setup' => $setupDefaults,
            'merge' => [
                'servers' => [
                    [
                        'url' => 'https://api.example.com/secured/{version}',
                        'variables' => ['version' => ['default' => 'v1']],
                    ],
                ],
            ],
            'assert' => [
                'servers' => [
                    [
                        'url' => 'https://api.example.com/secured/{version}',
                        'variables' => ['version' => ['default' => 'v1']],
                    ],
                ],
            ] + $assertDefaults,
        ];

        yield [
            // indexed collection merge
            'setup' => [
                'components' => self::createObj(OA\Components::class, [
                    'schemas' => [
                        self::createObj(OA\Schema::class, ['schema' => $no, 'title' => $no]),
                    ],
                ]),
            ] + $setupDefaults,
            'merge' => [
                'components' => [
                    'schemas' => [
                        $no => ['title' => $yes, 'description' => $yes],
                    ],
                ],
            ],
            'assert' => [
                'components' => [
                    'schemas' => [
                        $no => ['title' => $no, 'description' => $yes],
                    ],
                ],
            ] + $assertDefaults,
        ];

        yield [
            // collection merge
            'setup' => [
                'tags' => [self::createObj(OA\Tag::class, ['name' => $no])],
            ] + $setupDefaults,
            'merge' => [
                'tags' => [
                    // this is actually appending right now, no clue if this is wanted,
                    // but the complete NelmioApiDocBundle test suite is not upset by this fact
                    ['name' => $yes],
                    // this should not append since a tag with exactly the same properties
                    // is already present
                    ['name' => $no],
                    // this does, but should not append since the name already exists, and the
                    // docs in Tag state that the tag names must be unique, but it is complicated
                    // and $api->validate() does not complain either
                    ['name' => $no, 'description' => $yes],
                ],
            ],
            'assert' => [
                'tags' => [
                    ['name' => $no],
                    ['name' => $yes],
                    ['name' => $no, 'description' => $yes],
                ],
            ] + $assertDefaults,
        ];

        yield [
            // heavy nested merge array
            'setup' => $setupDefaults,
            'merge' => $merge = [
                'servers' => [
                    ['url' => 'http'],
                    ['url' => 'https'],
                ],
                'paths' => [
                    '/path/to/resource' => [
                        'get' => [
                            'responses' => [
                                '200' => [
                                    '$ref' => '#/components/responses/default',
                                ],
                            ],
                            'requestBody' => [
                                'description' => 'request foo',
                                'content' => [
                                    'foo-request' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'required' => ['baz', 'bar'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'tags' => [
                    ['name' => 'baz'],
                    ['name' => 'foo'],
                    ['name' => 'baz'],
                    ['name' => 'foo'],
                    ['name' => 'foo'],
                ],
                'components' => [
                    'responses' => [
                        'default' => [
                            'description' => 'default response',
                            'headers' => [
                                'foo-header' => [
                                    'schema' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'string',
                                            'enum' => ['foo', 'bar', 'baz'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'assert' => array_merge(
                $assertDefaults,
                $merge,
                ['tags' => \array_slice($merge['tags'], 0, 2, true)]
            ),
        ];

        yield [
            // heavy nested merge array object
            'setup' => $setupDefaults,
            'merge' => new \ArrayObject([
                'servers' => [
                    ['url' => 'http'],
                    ['url' => 'https'],
                ],
                'paths' => [
                    '/path/to/resource' => [
                        'get' => new \ArrayObject([
                            'responses' => [
                                '200' => [
                                    '$ref' => '#/components/responses/default',
                                ],
                            ],
                            'requestBody' => new \ArrayObject([
                                'description' => 'request foo',
                                'content' => [
                                    'foo-request' => [
                                        'schema' => [
                                            'required' => ['baz', 'bar'],
                                            'type' => 'object',
                                        ],
                                    ],
                                ],
                            ]),
                        ]),
                    ],
                ],
                'tags' => new \ArrayObject([
                    ['name' => 'baz'],
                    ['name' => 'foo'],
                    new \ArrayObject(['name' => 'baz']),
                    ['name' => 'foo'],
                    ['name' => 'foo'],
                ]),
                'components' => new \ArrayObject([
                    'responses' => [
                        'default' => [
                            'description' => 'default response',
                            'headers' => new \ArrayObject([
                                'foo-header' => new \ArrayObject([
                                    'schema' => new \ArrayObject([
                                        'type' => 'array',
                                        'items' => new \ArrayObject([
                                            'type' => 'string',
                                            'enum' => ['foo', 'bar', 'baz'],
                                        ]),
                                    ]),
                                ]),
                            ]),
                        ],
                    ],
                ]),
            ]),
            'assert' => array_merge(
                $assertDefaults,
                $merge,
                ['tags' => \array_slice($merge['tags'], 0, 2, true)]
            ),
        ];

        yield [
            // heavy nested merge swagger instance
            'setup' => $setupDefaults,
            'merge' => self::createObj(OA\OpenApi::class, [
                'servers' => [
                    self::createObj(OA\Server::class, ['url' => 'http']),
                    self::createObj(OA\Server::class, ['url' => 'https']),
                ],
                'paths' => [
                    self::createObj(OA\PathItem::class, [
                        'path' => '/path/to/resource',
                        'get' => self::createObj(OA\Get::class, [
                            'responses' => [
                                self::createObj(OA\Response::class, [
                                    'response' => '200',
                                    'ref' => '#/components/responses/default',
                                ]),
                            ],
                            'requestBody' => self::createObj(OA\RequestBody::class, [
                                'description' => 'request foo',
                                'content' => [
                                    self::createObj(OA\MediaType::class, [
                                        'mediaType' => 'foo-request',
                                        'schema' => self::createObj(OA\Schema::class, [
                                            'type' => 'object',
                                            'required' => ['baz', 'bar'],
                                        ]),
                                    ]),
                                ],
                            ]),
                        ]),
                    ]),
                ],
                'tags' => [
                    self::createObj(OA\Tag::class, ['name' => 'baz']),
                    self::createObj(OA\Tag::class, ['name' => 'foo']),
                    self::createObj(OA\Tag::class, ['name' => 'baz']),
                    self::createObj(OA\Tag::class, ['name' => 'foo']),
                    self::createObj(OA\Tag::class, ['name' => 'foo']),
                ],
                'components' => self::createObj(OA\Components::class, [
                    'responses' => [
                        self::createObj(OA\Response::class, [
                            'response' => 'default',
                            'description' => 'default response',
                            'headers' => [
                                self::createObj(OA\Header::class, [
                                    'header' => 'foo-header',
                                    'schema' => self::createObj(OA\Schema::class, [
                                        'type' => 'array',
                                        'items' => self::createObj(OA\Items::class, [
                                            'type' => 'string',
                                            'enum' => ['foo', 'bar', 'baz'],
                                        ]),
                                    ]),
                                ]),
                            ],
                        ]),
                    ],
                ]),
            ]),
            'assert' => array_merge(
                $assertDefaults,
                $merge,
                ['tags' => \array_slice($merge['tags'], 0, 2, true)]
            ),
        ];
    }

    public function assertIsNested(OA\AbstractAnnotation $parent, OA\AbstractAnnotation $child): void
    {
        self::assertTrue($child->_context->is('nested'));
        self::assertSame($parent, $child->_context->nested);
    }

    public function assertIsConnectedToRootContext(OA\AbstractAnnotation $annotation): void
    {
        $this->assertContextIsConnectedToRootContext($annotation->_context);
    }

    public function assertContextIsConnectedToRootContext(Context $context): void
    {
        self::assertSame($this->rootContext, $context->root());
    }

    /**
     * @param array<mixed> $setup
     *
     * @return array<mixed>
     */
    private function getSetupPropertiesWithoutClass(array $setup): array
    {
        return array_filter($setup, function ($k) {return 'class' !== $k; }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @return array<mixed>
     */
    private function getNonDefaultProperties(OA\AbstractAnnotation $object): array
    {
        $objectVars = \get_object_vars($object);
        $classVars = \get_class_vars(\get_class($object));
        $props = [];
        foreach ($objectVars as $key => $value) {
            if ($value !== $classVars[$key] && 0 !== \strpos($key, '_')) {
                $props[$key] = $value;
            }
        }

        return $props;
    }

    /**
     * @param class-string<OA\AbstractAnnotation> $className
     * @param array<string, mixed>                $props
     */
    private static function createObj(string $className, array $props = []): object
    {
        return new $className($props + ['_context' => new Context()]);
    }
}
