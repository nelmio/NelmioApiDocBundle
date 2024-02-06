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

use ArrayObject;
use Exception;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use stdClass;
use function array_key_exists;
use function array_slice;
use function count;
use function get_class;
use function get_class_vars;
use function get_object_vars;
use function in_array;
use function strpos;

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
    public $rootContext;

    /** @var OA\OpenApi */
    public $rootAnnotation;

    public function setUp(): void
    {
        parent::setUp();

        $this->rootContext = new Context(['isTestingRoot' => true]);
        $this->rootAnnotation = $this->createObj(OA\OpenApi::class, ['_context' => $this->rootContext]);
    }

    public function testCreateContextSetsParentContext()
    {
        $context = Util::createContext([], $this->rootContext);

        $this->assertContextIsConnectedToRootContext($context);
    }

    public function testCreateContextWithProperties()
    {
        $context = Util::createContext(['testing' => 'trait']);

        $this->assertTrue($context->is('testing'));
        $this->assertSame('trait', $context->testing);
    }

    public function testCreateChild()
    {
        $info = Util::createChild($this->rootAnnotation, OA\Info::class);

        $this->assertInstanceOf(OA\Info::class, $info);
    }

    public function testCreateChildHasContext()
    {
        $info = Util::createChild($this->rootAnnotation, OA\Info::class);

        $this->assertInstanceOf(Context::class, $info->_context);
    }

    public function testCreateChildHasNestedContext()
    {
        $path = Util::createChild($this->rootAnnotation, OA\PathItem::class);
        $this->assertIsNested($this->rootAnnotation, $path);

        $parameter = Util::createChild($path, OA\Parameter::class);
        $this->assertIsNested($path, $parameter);

        $schema = Util::createChild($parameter, OA\Schema::class);
        $this->assertIsNested($parameter, $schema);

        $this->assertIsConnectedToRootContext($schema);
    }

    public function testCreateChildWithEmptyProperties()
    {
        $properties = [];
        /** @var OA\Info $info */
        $info = Util::createChild($this->rootAnnotation, OA\Info::class, $properties);

        $properties = array_filter(get_object_vars($info), function ($key) {
            return 0 !== strpos($key, '_');
        }, ARRAY_FILTER_USE_KEY);

        $this->assertEquals([Generator::UNDEFINED], array_unique(array_values($properties)));

        $this->assertIsNested($this->rootAnnotation, $info);
        $this->assertIsConnectedToRootContext($info);
    }

    public function testCreateChildWithProperties()
    {
        $properties = ['title' => 'testing', 'version' => '999', 'x' => new stdClass()];
        /** @var OA\Info $info */
        $info = Util::createChild($this->rootAnnotation, OA\Info::class, $properties);

        $this->assertSame($info->title, $properties['title']);
        $this->assertSame($info->version, $properties['version']);
        $this->assertSame($info->x, $properties['x']);

        $this->assertIsNested($this->rootAnnotation, $info);
        $this->assertIsConnectedToRootContext($info);
    }

    public function testCreateCollectionItemAddsCreatedItemToCollection()
    {
        $collection = 'paths';
        $class = OA\PathItem::class;

        $p1 = Util::createCollectionItem($this->rootAnnotation, $collection, $class);
        $this->assertSame(0, $p1);
        $this->assertCount(1, $this->rootAnnotation->{$collection});
        $this->assertInstanceOf($class, $this->rootAnnotation->{$collection}[$p1]);
        $this->assertIsNested($this->rootAnnotation, $this->rootAnnotation->{$collection}[$p1]);
        $this->assertIsConnectedToRootContext($this->rootAnnotation->{$collection}[$p1]);

        $p2 = Util::createCollectionItem($this->rootAnnotation, $collection, $class);
        $this->assertSame(1, $p2);
        $this->assertCount(2, $this->rootAnnotation->{$collection});
        $this->assertInstanceOf($class, $this->rootAnnotation->{$collection}[$p2]);
        $this->assertIsNested($this->rootAnnotation, $this->rootAnnotation->{$collection}[$p2]);
        $this->assertIsConnectedToRootContext($this->rootAnnotation->{$collection}[$p2]);

        $this->rootAnnotation->components = Util::createChild($this->rootAnnotation, OA\Components::class);

        $collection = 'schemas';
        $class = OA\Schema::class;

        $d1 = Util::createCollectionItem($this->rootAnnotation->components, $collection, $class);
        $this->assertSame(0, $d1);
        $this->assertCount(1, $this->rootAnnotation->components->{$collection});
        $this->assertInstanceOf($class, $this->rootAnnotation->components->{$collection}[$d1]);
        $this->assertIsNested($this->rootAnnotation->components, $this->rootAnnotation->components->{$collection}[$d1]);
        $this->assertIsConnectedToRootContext($this->rootAnnotation->components->{$collection}[$d1]);
    }

    public function testCreateCollectionItemDoesNotAddToUnknownProperty()
    {
        $collection = 'foobars';
        $class = OA\Info::class;

        $expectedRegex = "/Property \"{$collection}\" doesn't exist .*/";
        set_error_handler(function ($_, $err) { echo $err; });
        $this->expectOutputRegex($expectedRegex);
        Util::createCollectionItem($this->rootAnnotation, $collection, $class);
        $this->expectOutputRegex($expectedRegex);
        $this->assertNull($this->rootAnnotation->{$collection});
        restore_error_handler();
    }

    public function testSearchCollectionItem()
    {
        $item1 = new stdClass();
        $item1->prop1 = 'item 1 prop 1';
        $item1->prop2 = 'item 1 prop 2';
        $item1->prop3 = 'item 1 prop 3';

        $item2 = new stdClass();
        $item2->prop1 = 'item 2 prop 1';
        $item2->prop2 = 'item 2 prop 2';
        $item2->prop3 = 'item 2 prop 3';

        $collection = [
            $item1,
            $item2,
        ];

        $this->assertSame(0, Util::searchCollectionItem($collection, get_object_vars($item1)));
        $this->assertSame(1, Util::searchCollectionItem($collection, get_object_vars($item2)));

        $this->assertNull(Util::searchCollectionItem(
            $collection,
            array_merge(get_object_vars($item2), ['prop3' => 'foobar'])
        ));

        $search = ['baz' => 'foobar'];
        $this->expectOutputString('Undefined property: stdClass::$baz');

        try {
            Util::searchCollectionItem($collection, array_merge(get_object_vars($item2), $search));
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        // no exception on empty collection
        $this->assertNull(Util::searchCollectionItem([], get_object_vars($item2)));
    }

    /**
     * @dataProvider provideIndexedCollectionData
     */
    public function testSearchIndexedCollectionItem($setup, $asserts)
    {
        foreach ($asserts as $collection => $items) {
            foreach ($items as $assert) {
                $setupCollection = empty($assert['components']) ?
                    ($setup[$collection] ?? []) :
                    (Generator::UNDEFINED !== $setup['components']->{$collection} ? $setup['components']->{$collection} : []);

                // get the indexing correct within haystack preparation
                $properties = array_fill(0, count($setupCollection), null);

                // prepare the haystack array
                foreach ($items as $assertItem) {
                    // e.g. $properties[1] = $this->createObj(OA\PathItem::class, ['path' => 'path 1'])
                    $properties[$assertItem['index']] = $this->createObj($assertItem['class'], [
                        $assertItem['key'] => $assertItem['value'],
                    ]);
                }

                $this->assertSame(
                    $assert['index'],
                    Util::searchIndexedCollectionItem($properties, $assert['key'], $assert['value']),
                    sprintf('Failed to get the correct index for %s', print_r($assert, true))
                );
            }
        }
    }

    /**
     * @dataProvider provideIndexedCollectionData
     */
    public function testGetIndexedCollectionItem($setup, $asserts)
    {
        $parent = new $setup['class'](array_merge(
            $this->getSetupPropertiesWithoutClass($setup),
            ['_context' => $this->rootContext]
        ));

        foreach ($asserts as $collection => $items) {
            foreach ($items as $assert) {
                $itemParent = empty($assert['components']) ? $parent : $parent->components;

                $child = Util::getIndexedCollectionItem(
                    $itemParent,
                    $assert['class'],
                    $assert['value']
                );

                $this->assertInstanceOf($assert['class'], $child);
                $this->assertSame($child->{$assert['key']}, $assert['value']);
                $this->assertSame(
                    $itemParent->{$collection}[$assert['index']],
                    $child
                );

                $setupHaystack = empty($assert['components']) ?
                    $setup[$collection] ?? [] :
                    $setup['components']->{$collection} ?? [];

                // the children created within provider are not connected
                if (!in_array($child, $setupHaystack, true)) {
                    $this->assertIsNested($itemParent, $child);
                    $this->assertIsConnectedToRootContext($child);
                }
            }
        }
    }

    public function provideIndexedCollectionData(): array
    {
        return [[
            'setup' => [
                'class' => OA\OpenApi::class,
                'paths' => [
                    $this->createObj(OA\PathItem::class, ['path' => 'path 0']),
                ],
                'components' => $this->createObj(OA\Components::class, [
                    'parameters' => [
                        $this->createObj(OA\Parameter::class, ['parameter' => 'parameter 0']),
                        $this->createObj(OA\Parameter::class, ['parameter' => 'parameter 1']),
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
        ]];
    }

    /**
     * @dataProvider provideChildData
     */
    public function testGetChild($setup, $asserts)
    {
        $parent = new $setup['class'](array_merge(
            $this->getSetupPropertiesWithoutClass($setup),
            ['_context' => $this->rootContext]
        ));

        foreach ($asserts as $key => $assert) {
            if (array_key_exists('exceptionMessage', $assert)) {
                $this->expectExceptionMessage($assert['exceptionMessage']);
            }
            $child = Util::getChild($parent, $assert['class'], $assert['props']);

            $this->assertInstanceOf($assert['class'], $child);
            $this->assertSame($child, $parent->{$key});

            if (array_key_exists($key, $setup)) {
                $this->assertSame($setup[$key], $parent->{$key});
            }

            $this->assertEquals($assert['props'], $this->getNonDefaultProperties($child));
        }
    }

    public function provideChildData(): array
    {
        return [[
            'setup' => [
                'class' => OA\PathItem::class,
                'get' => $this->createObj(OA\Get::class, []),
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
        ], [
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
        ], [
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
        ]];
    }

    public function testGetOperationParameterReturnsExisting()
    {
        $name = 'operation name';
        $in = 'operation in';

        $parameter = $this->createObj(OA\Parameter::class, ['name' => $name, 'in' => $in]);
        $operation = $this->createObj(OA\Get::class, ['parameters' => [
            $this->createObj(OA\Parameter::class, []),
            $this->createObj(OA\Parameter::class, ['name' => 'foo']),
            $this->createObj(OA\Parameter::class, ['in' => 'bar']),
            $this->createObj(OA\Parameter::class, ['name' => $name, 'in' => 'bar']),
            $this->createObj(OA\Parameter::class, ['name' => 'foo', 'in' => $in]),
            $parameter,
        ]]);

        $actual = Util::getOperationParameter($operation, $name, $in);
        $this->assertSame($parameter, $actual);
    }

    public function testGetOperationParameterCreatesWithNameAndIn()
    {
        $name = 'operation name';
        $in = 'operation in';

        $operation = $this->createObj(OA\Get::class, ['parameters' => [
            $this->createObj(OA\Parameter::class, []),
            $this->createObj(OA\Parameter::class, ['name' => 'foo']),
            $this->createObj(OA\Parameter::class, ['in' => 'bar']),
            $this->createObj(OA\Parameter::class, ['name' => $name, 'in' => 'bar']),
            $this->createObj(OA\Parameter::class, ['name' => 'foo', 'in' => $in]),
        ]]);

        $actual = Util::getOperationParameter($operation, $name, $in);
        $this->assertInstanceOf(OA\Parameter::class, $actual);
        $this->assertSame($name, $actual->name);
        $this->assertSame($in, $actual->in);
    }

    public function testGetOperationReturnsExisting()
    {
        $get = $this->createObj(OA\Get::class, []);
        $path = $this->createObj(OA\PathItem::class, ['get' => $get]);

        $this->assertSame($get, Util::getOperation($path, 'get'));
    }

    public function testGetOperationCreatesWithPath()
    {
        $pathStr = '/testing/get/path';
        $path = $this->createObj(OA\PathItem::class, ['path' => $pathStr]);

        $get = Util::getOperation($path, 'get');
        $this->assertInstanceOf(OA\Get::class, $get);
        $this->assertSame($pathStr, $get->path);
    }

    public function testMergeWithEmptyArray()
    {
        $api = $this->createObj(OA\OpenApi::class, ['_context' => new Context()]);
        $expected = json_encode($api);

        Util::merge($api, [], false);
        $actual = json_encode($api);

        $this->assertSame($expected, $actual);

        Util::merge($api, [], true);
        $actual = json_encode($api);

        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider provideMergeData
     */
    public function testMerge($setup, $merge, $assert)
    {
        $api = $this->createObj(OA\OpenApi::class, $setup + ['_context' => new Context()]);

        Util::merge($api, $merge, false);
        $this->assertTrue($api->validate());
        $actual = json_decode(json_encode($api), true);

        $this->assertEquals($assert, $actual);
    }

    public function provideMergeData(): array
    {
        $no = 'do not overwrite';
        $yes = 'do overwrite';

        $requiredInfo = ['title' => '', 'version' => ''];

        $setupDefaults = [
            'info' => $this->createObj(OA\Info::class, $requiredInfo),
            'paths' => [],
        ];
        $assertDefaults = [
            'info' => $requiredInfo,
            'openapi' => '3.0.0',
            'paths' => [],
        ];

        return [[
            // simple child merge
            'setup' => [
                'info' => $this->createObj(OA\Info::class, ['version' => $no]),
                'paths' => [],
            ],
            'merge' => [
                'info' => ['title' => $yes, 'version' => $yes],
            ],
            'assert' => [
                    'info' => ['title' => $yes, 'version' => $no],
                ] + $assertDefaults,
        ], [
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
        ], [
            // indexed collection merge
            'setup' => [
                    'components' => $this->createObj(OA\Components::class, [
                        'schemas' => [
                            $this->createObj(OA\Schema::class, ['schema' => $no, 'title' => $no]),
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
        ], [
            // collection merge
            'setup' => [
                    'tags' => [$this->createObj(OA\Tag::class, ['name' => $no])],
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
        ],
            [
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
                    ['tags' => array_slice($merge['tags'], 0, 2, true)]
                ),
            ], [
                // heavy nested merge array object
                'setup' => $setupDefaults,
                'merge' => new ArrayObject([
                    'servers' => [
                        ['url' => 'http'],
                        ['url' => 'https'],
                    ],
                    'paths' => [
                        '/path/to/resource' => [
                            'get' => new ArrayObject([
                                'responses' => [
                                    '200' => [
                                        '$ref' => '#/components/responses/default',
                                    ],
                                ],
                                'requestBody' => new ArrayObject([
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
                    'tags' => new ArrayObject([
                        ['name' => 'baz'],
                        ['name' => 'foo'],
                        new ArrayObject(['name' => 'baz']),
                        ['name' => 'foo'],
                        ['name' => 'foo'],
                    ]),
                    'components' => new ArrayObject([
                        'responses' => [
                            'default' => [
                                'description' => 'default response',
                                'headers' => new ArrayObject([
                                    'foo-header' => new ArrayObject([
                                        'schema' => new ArrayObject([
                                            'type' => 'array',
                                            'items' => new ArrayObject([
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
                    ['tags' => array_slice($merge['tags'], 0, 2, true)]
                ),
            ], [
                // heavy nested merge swagger instance
                'setup' => $setupDefaults,
                'merge' => $this->createObj(OA\OpenApi::class, [
                    'servers' => [
                        $this->createObj(OA\Server::class, ['url' => 'http']),
                        $this->createObj(OA\Server::class, ['url' => 'https']),
                    ],
                    'paths' => [
                        $this->createObj(OA\PathItem::class, [
                            'path' => '/path/to/resource',
                            'get' => $this->createObj(OA\Get::class, [
                                'responses' => [
                                    $this->createObj(OA\Response::class, [
                                        'response' => '200',
                                        'ref' => '#/components/responses/default',
                                    ]),
                                ],
                                'requestBody' => $this->createObj(OA\RequestBody::class, [
                                    'description' => 'request foo',
                                    'content' => [
                                        $this->createObj(OA\MediaType::class, [
                                            'mediaType' => 'foo-request',
                                            'schema' => $this->createObj(OA\Schema::class, [
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
                        $this->createObj(OA\Tag::class, ['name' => 'baz']),
                        $this->createObj(OA\Tag::class, ['name' => 'foo']),
                        $this->createObj(OA\Tag::class, ['name' => 'baz']),
                        $this->createObj(OA\Tag::class, ['name' => 'foo']),
                        $this->createObj(OA\Tag::class, ['name' => 'foo']),
                    ],
                    'components' => $this->createObj(OA\Components::class, [
                        'responses' => [
                            $this->createObj(OA\Response::class, [
                                'response' => 'default',
                                'description' => 'default response',
                                'headers' => [
                                    $this->createObj(OA\Header::class, [
                                        'header' => 'foo-header',
                                        'schema' => $this->createObj(OA\Schema::class, [
                                            'type' => 'array',
                                            'items' => $this->createObj(OA\Items::class, [
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
                    ['tags' => array_slice($merge['tags'], 0, 2, true)]
                ),
            ], ];
    }

    public function assertIsNested(OA\AbstractAnnotation $parent, OA\AbstractAnnotation $child)
    {
        self::assertTrue($child->_context->is('nested'));
        self::assertSame($parent, $child->_context->nested);
    }

    public function assertIsConnectedToRootContext(OA\AbstractAnnotation $annotation)
    {
        $this->assertContextIsConnectedToRootContext($annotation->_context);
    }

    public function assertContextIsConnectedToRootContext(Context $context)
    {
        $this->assertSame($this->rootContext, $context->root());
    }

    private function getSetupPropertiesWithoutClass(array $setup)
    {
        return array_filter($setup, function ($k) {return 'class' !== $k; }, ARRAY_FILTER_USE_KEY);
    }

    private function getNonDefaultProperties($object)
    {
        $objectVars = get_object_vars($object);
        $classVars = get_class_vars(get_class($object));
        $props = [];
        foreach ($objectVars as $key => $value) {
            if ($value !== $classVars[$key] && 0 !== strpos($key, '_')) {
                $props[$key] = $value;
            }
        }

        return $props;
    }

    private function createObj(string $className, array $props = [])
    {
        return new $className($props + ['_context' => new Context()]);
    }
}
