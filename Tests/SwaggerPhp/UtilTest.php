<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests;

use Nelmio\ApiDocBundle\SwaggerPhp\Util;
use PHPUnit\Framework\TestCase;
use Swagger\Annotations\AbstractAnnotation;
use Swagger\Annotations\Definition;
use Swagger\Annotations\Delete;
use Swagger\Annotations\Get;
use Swagger\Annotations\Header;
use Swagger\Annotations\Info;
use Swagger\Annotations\Items;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Path;
use Swagger\Annotations\Put;
use Swagger\Annotations\Response;
use Swagger\Annotations\Schema;
use Swagger\Annotations\SecurityScheme;
use Swagger\Annotations\Swagger;
use Swagger\Annotations\Tag;
use Swagger\Context;

/**
 * Class UtilTest.
 *
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::getOperation
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::getOperationParameter
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::getChild
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::getCollectionItem
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::getIndexedCollectionItem
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::searchCollectionItem
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::searchIndexedCollectionItem
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::createCollectionItem
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::createChild
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::createContext
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::merge
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::mergeFromArray
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::mergeChild
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::mergeCollection
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::mergeTyped
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::mergeProperty
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::getNestingIndexes
 * @covers \Nelmio\ApiDocBundle\SwaggerPhp\Util::getNesting
 */
class UtilTest extends TestCase
{
    public $rootContext;
    public $rootAnnotation;

    public function setUp()
    {
        parent::setUp();

        $this->rootContext = new Context(['isTestingRoot' => true]);
        $this->rootAnnotation = new Swagger(['_context' => $this->rootContext]);
    }

    public function testCreateContextSetsParentContext()
    {
        $context = Util::createContext([], $this->rootContext);

        $this->assertSame($this->rootContext, $context->getRootContext());
    }

    public function testCreateContextWithProperties()
    {
        $context = Util::createContext(['testing' => 'trait']);

        $this->assertTrue($context->is('testing'));
        $this->assertSame('trait', $context->testing);
    }

    public function testCreateChild()
    {
        $info = Util::createChild($this->rootAnnotation, Info::class);

        $this->assertInstanceOf(Info::class, $info);
    }

    public function testCreateChildHasContext()
    {
        $info = Util::createChild($this->rootAnnotation, Info::class);

        $this->assertInstanceOf(Context::class, $info->_context);
    }

    public function testCreateChildHasNestedContext()
    {
        $path = Util::createChild($this->rootAnnotation, Path::class);
        $this->assertIsNested($this->rootAnnotation, $path);

        $parameter = Util::createChild($path, Parameter::class);
        $this->assertIsNested($path, $parameter);

        $schema = Util::createChild($parameter, Schema::class);
        $this->assertIsNested($parameter, $schema);

        $this->assertIsConnectedToRootContext($schema);
    }

    public function testCreateChildWithEmptyProperties()
    {
        $properties = [];
        /** @var Info $info */
        $info = Util::createChild($this->rootAnnotation, Info::class, $properties);

        $properties = array_filter(get_object_vars($info), function ($key) {
            return 0 !== strpos($key, '_');
        }, ARRAY_FILTER_USE_KEY);

        $this->assertEquals([null], array_unique(array_values($properties)));

        $this->assertIsNested($this->rootAnnotation, $info);
        $this->assertIsConnectedToRootContext($info);
    }

    public function testCreateChildWithProperties()
    {
        $properties = ['title' => 'testing', 'version' => '999', 'x' => new \stdClass()];
        /** @var Info $info */
        $info = Util::createChild($this->rootAnnotation, Info::class, $properties);

        $this->assertSame($info->title, $properties['title']);
        $this->assertSame($info->version, $properties['version']);
        $this->assertSame($info->x, $properties['x']);

        $this->assertIsNested($this->rootAnnotation, $info);
        $this->assertIsConnectedToRootContext($info);
    }

    public function testCreateCollectionItemAddsCreatedItemToCollection()
    {
        $collection = 'paths';
        $class = Path::class;

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

        $collection = 'definitions';
        $class = Definition::class;

        $d1 = Util::createCollectionItem($this->rootAnnotation, $collection, $class);
        $this->assertSame(0, $d1);
        $this->assertCount(1, $this->rootAnnotation->{$collection});
        $this->assertInstanceOf($class, $this->rootAnnotation->{$collection}[$d1]);
        $this->assertIsNested($this->rootAnnotation, $this->rootAnnotation->{$collection}[$d1]);
        $this->assertIsConnectedToRootContext($this->rootAnnotation->{$collection}[$d1]);
    }

    public function testCreateCollectionItemDoesNotAddToUnknownProperty()
    {
        $collection = 'foobars';
        $class = Info::class;

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
        } catch (\Exception $e) {
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
            $preset = \count($setup[$collection] ?? []);
            // get the indexing correct within haystack preparation
            $properties = array_fill(0, $preset, null);

            // prepare the haystack array
            foreach ($items as $assert) {
                // e.g. $properties[1] = new Path(['path' => 'path 1'])
                $properties[$assert['index']] = new $assert['class']([
                    $assert['key'] => $assert['value'],
                ]);
            }
            foreach ($items as $assert) {
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
                $child = Util::getIndexedCollectionItem(
                    $parent, $assert['class'], $assert['value']
                );
                $this->assertInstanceOf($assert['class'], $child);
                $this->assertSame($child->{$assert['key']}, $assert['value']);
                $this->assertSame(
                    $parent->{$collection}[$assert['index']],
                    $child
                );
                // the children created within provider are not connected
                if (!\in_array($child, $setup[$collection] ?? [], true)) {
                    $this->assertIsNested($parent, $child);
                    $this->assertIsConnectedToRootContext($child);
                }
            }
        }
    }

    public function provideIndexedCollectionData(): array
    {
        return [[
            'setup' => [
                'class' => Swagger::class,
                'paths' => [
                    new Path(['path' => 'path 0']),
                ],
                'parameters' => [
                    new Parameter(['parameter' => 'parameter 0']),
                    new Parameter(['parameter' => 'parameter 1']),
                ],
            ],
            'assert' => [
                // one fixed within setup and one dynamically created
                'paths' => [
                    [
                        'index' => 0,
                        'class' => Path::class,
                        'key' => 'path',
                        'value' => 'path 0',
                    ],
                    [
                        'index' => 1,
                        'class' => Path::class,
                        'key' => 'path',
                        'value' => 'path 1',
                    ],
                ],
                // not contained in setup
                'definitions' => [
                    [
                        'index' => 0,
                        'class' => Definition::class,
                        'key' => 'definition',
                        'value' => 'definition 0',
                    ],
                ],
                // search indexes out of order followed by dynamically created
                'parameters' => [
                    [
                        'index' => 1,
                        'class' => Parameter::class,
                        'key' => 'parameter',
                        'value' => 'parameter 1',
                    ],
                    [
                        'index' => 0,
                        'class' => Parameter::class,
                        'key' => 'parameter',
                        'value' => 'parameter 0',
                    ],
                    [
                        'index' => 2,
                        'class' => Parameter::class,
                        'key' => 'parameter',
                        'value' => 'parameter 2',
                    ],
                ],
                // two dynamically created
                'responses' => [
                    [
                        'index' => 0,
                        'class' => Response::class,
                        'key' => 'response',
                        'value' => 'response 0',
                    ],
                    [
                        'index' => 1,
                        'class' => Response::class,
                        'key' => 'response',
                        'value' => 'response 1',
                    ],
                ],
                // for sake of completeness
                'securityDefinitions' => [
                    [
                        'index' => 0,
                        'class' => SecurityScheme::class,
                        'key' => 'securityDefinition',
                        'value' => 'securityDefinition 0',
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

            if (\array_key_exists($key, $setup)) {
                $this->assertSame($setup[$key], $parent->{$key});
            }

            $this->assertEquals($assert['props'], $this->getNonDefaultProperties($child));
        }
    }

    public function provideChildData(): array
    {
        return [[
            'setup' => [
                'class' => Path::class,
                'get' => new Get([]),
            ],
            'assert' => [
                // fixed within setup
                'get' => [
                    'class' => Get::class,
                    'props' => [],
                ],
                // create new without props
                'put' => [
                    'class' => Put::class,
                    'props' => [],
                ],
                // create new with multiple props
                'delete' => [
                    'class' => Delete::class,
                    'props' => [
                        'summary' => 'testing delete',
                        'deprecated' => true,
                    ],
                ],
            ],
        ], [
            'setup' => [
                'class' => Parameter::class,
                'items' => new Items([]),
            ],
            'assert' => [
                // fixed within setup
                'items' => [
                    'class' => Items::class,
                    'props' => [],
                ],
                // create new with multiple props
                'schema' => [
                    'class' => Schema::class,
                    'props' => [
                        'ref' => '#/testing/schema',
                        'minProperties' => 0,
                        'enum' => [null, 'check', 999, false],
                    ],
                ],
            ],
        ], [
            'setup' => [
                'class' => Parameter::class,
            ],
            'assert' => [
                // externalDocs triggers invalid argument exception
                'items' => [
                    'class' => Items::class,
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

        $parameter = new Parameter(['name' => $name, 'in' => $in]);
        $operation = new Get(['parameters' => [
            new Parameter([]),
            new Parameter(['name' => 'foo']),
            new Parameter(['in' => 'bar']),
            new Parameter(['name' => $name, 'in' => 'bar']),
            new Parameter(['name' => 'foo', 'in' => $in]),
            $parameter,
        ]]);

        $actual = Util::getOperationParameter($operation, $name, $in);
        $this->assertSame($parameter, $actual);
    }

    public function testGetOperationParameterCreatesWithNameAndIn()
    {
        $name = 'operation name';
        $in = 'operation in';

        $operation = new Get(['parameters' => [
            new Parameter([]),
            new Parameter(['name' => 'foo']),
            new Parameter(['in' => 'bar']),
            new Parameter(['name' => $name, 'in' => 'bar']),
            new Parameter(['name' => 'foo', 'in' => $in]),
        ]]);

        $actual = Util::getOperationParameter($operation, $name, $in);
        $this->assertInstanceOf(Parameter::class, $actual);
        $this->assertSame($name, $actual->name);
        $this->assertSame($in, $actual->in);
    }

    public function testGetOperationReturnsExisting()
    {
        $get = new Get([]);
        $path = new Path(['get' => $get]);

        $this->assertSame($get, Util::getOperation($path, 'get'));
    }

    public function testGetOperationCreatesWithPath()
    {
        $pathStr = '/testing/get/path';
        $path = new Path(['path' => $pathStr]);

        $get = Util::getOperation($path, 'get');
        $this->assertInstanceOf(Get::class, $get);
        $this->assertSame($pathStr, $get->path);
    }

    public function testMergeWithEmptyArray()
    {
        $api = new Swagger([]);
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
        $api = new Swagger($setup);

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

        $setupDefaults = ['info' => new Info($requiredInfo)];
        $assertDefaults = [
            'info' => $requiredInfo,
            'swagger' => '2.0',
            'paths' => [],
            'definitions' => [],
        ];

        return [[
            // simple child merge
            'setup' => ['info' => new Info(['version' => $no])],
            'merge' => ['info' => ['title' => $yes, 'version' => $yes]],
            'assert' => [
                'info' => ['title' => $yes, 'version' => $no],
            ] + $assertDefaults,
        ], [
            // indexed collection merge
            'setup' => [
                'definitions' => [
                    new Definition(['definition' => $no, 'title' => $no]),
                ],
            ] + $setupDefaults,
            'merge' => [
                'definitions' => [
                    $no => ['title' => $yes, 'description' => $yes],
                ],
            ],
            'assert' => [
                'definitions' => [
                    $no => ['title' => $no, 'description' => $yes],
                ],
            ] + $assertDefaults,
        ], [
            // collection merge
            'setup' => [
                'tags' => [new Tag(['name' => $no])],
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
        ], [
            // heavy nested merge array
            'setup' => $setupDefaults,
            'merge' => $merge = [
                'schemes' => ['http', 'https'],
                'paths' => [
                    '/path/to/resource' => [
                        'get' => [
                            'responses' => [
                                '200' => [
                                    '$ref' => '#/responses/default',
                                ],
                            ],
                        ],
                        'parameters' => [[
                            'name' => 'parameter foo',
                            'in' => 'body',
                            'type' => 'object',
                            'schema' => [
                                'required' => ['baz', 'bar'],
                            ],
                        ]],
                    ],
                ],
                'tags' => [
                    ['name' => 'baz'],
                    ['name' => 'foo'],
                    ['name' => 'baz'],
                    ['name' => 'foo'],
                    ['name' => 'foo'],
                ],
                'responses' => [
                    'default' => [
                        'description' => 'default response',
                        'headers' => [
                            'foo-header' => [
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
            'assert' => array_merge(
                $assertDefaults,
                $merge,
                ['tags' => \array_slice($merge['tags'], 0, 2, true)]
            ),
        ], [
            // heavy nested merge array object
            'setup' => $setupDefaults,
            'merge' => new \ArrayObject([
                'schemes' => ['http', 'https'],
                'paths' => [
                    '/path/to/resource' => [
                        'get' => new \ArrayObject([
                            'responses' => [
                                '200' => [
                                    '$ref' => '#/responses/default',
                                ],
                            ],
                        ]),
                        'parameters' => [[
                            'name' => 'parameter foo',
                            'in' => 'body',
                            'type' => 'object',
                            'schema' => [
                                'required' => ['baz', 'bar'],
                            ],
                        ]],
                    ],
                ],
                'tags' => new \ArrayObject([
                    ['name' => 'baz'],
                    ['name' => 'foo'],
                    new \ArrayObject(['name' => 'baz']),
                    ['name' => 'foo'],
                    ['name' => 'foo'],
                ]),
                'responses' => [
                    'default' => [
                        'description' => 'default response',
                        'headers' => new \ArrayObject([
                            'foo-header' => new \ArrayObject([
                                'type' => 'array',
                                'items' => new \ArrayObject([
                                    'type' => 'string',
                                    'enum' => ['foo', 'bar', 'baz'],
                                ]),
                            ]),
                        ]),
                    ],
                ],
            ]),
            'assert' => array_merge(
                $assertDefaults,
                $merge,
                ['tags' => \array_slice($merge['tags'], 0, 2, true)]
            ),
        ], [
            // heavy nested merge swagger instance
            'setup' => $setupDefaults,
            'merge' => new Swagger([
                'schemes' => ['http', 'https'],
                'paths' => [
                    new Path([
                        'path' => '/path/to/resource',
                        'get' => new Get([
                            'responses' => [
                                new Response([
                                    'response' => '200',
                                    'ref' => '#/responses/default',
                                ]),
                            ],
                        ]),
                        'parameters' => [
                            new Parameter([
                                'name' => 'parameter foo',
                                'in' => 'body',
                                'type' => 'object',
                                'schema' => new Schema([
                                    'required' => ['baz', 'bar'],
                                ]),
                            ]),
                        ],
                    ]),
                ],
                'tags' => [
                    new Tag(['name' => 'baz']),
                    new Tag(['name' => 'foo']),
                    new Tag(['name' => 'baz']),
                    new Tag(['name' => 'foo']),
                    new Tag(['name' => 'foo']),
                ],
                'responses' => [
                    new Response([
                        'response' => 'default',
                        'description' => 'default response',
                        'headers' => [
                            new Header([
                                'header' => 'foo-header',
                                'type' => 'array',
                                'items' => new Items([
                                    'type' => 'string',
                                    'enum' => ['foo', 'bar', 'baz'],
                                ]),
                            ]),
                        ],
                    ]),
                ],
            ]),
            'assert' => array_merge(
                $assertDefaults,
                $merge,
                ['tags' => \array_slice($merge['tags'], 0, 2, true)]
            ),
        ]];
    }

    public function assertIsNested(AbstractAnnotation $parent, AbstractAnnotation $child)
    {
        self::assertTrue($child->_context->is('nested'));
        self::assertSame($parent, $child->_context->nested);
    }

    public function assertIsConnectedToRootContext(AbstractAnnotation $annotation)
    {
        $this->assertSame($this->rootContext, $annotation->_context->getRootContext());
    }

    private function getSetupPropertiesWithoutClass(array $setup)
    {
        return array_filter($setup, function ($k) {return 'class' !== $k; }, ARRAY_FILTER_USE_KEY);
    }

    private function getNonDefaultProperties($object)
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
}
