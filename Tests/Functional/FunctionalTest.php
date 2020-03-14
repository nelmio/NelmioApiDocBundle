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

use EXSyst\Component\Swagger\Tag;

class FunctionalTest extends WebTestCase
{
    protected function setUp()
    {
        parent::setUp();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);
    }

    public function testConfiguredDocumentation()
    {
        $this->assertEquals('My Default App', $this->getSwaggerDefinition()->getInfo()->getTitle());
        $this->assertEquals('My Test App', $this->getSwaggerDefinition('test')->getInfo()->getTitle());
    }

    public function testUndocumentedAction()
    {
        $paths = $this->getSwaggerDefinition()->getPaths();
        $this->assertFalse($paths->has('/undocumented'));
        $this->assertFalse($paths->has('/api/admin'));
    }

    public function testFetchArticleAction()
    {
        $operation = $this->getOperation('/api/article/{id}', 'get');

        $responses = $operation->getResponses();
        $this->assertTrue($responses->has('200'));
        $this->assertEquals('#/definitions/Article', $responses->get('200')->getSchema()->getRef());

        // Ensure that groups are supported
        $modelProperties = $this->getModel('Article')->getProperties();
        $this->assertCount(1, $modelProperties);
        $this->assertTrue($modelProperties->has('author'));
        $this->assertSame('#/definitions/User2', $modelProperties->get('author')->getRef());

        $this->assertFalse($modelProperties->has('content'));
    }

    public function testFilteredAction()
    {
        $paths = $this->getSwaggerDefinition()->getPaths();

        $this->assertFalse($paths->has('/filtered'));
    }

    /**
     * Tests that the paths are automatically resolved in Swagger annotations.
     *
     * @dataProvider swaggerActionPathsProvider
     */
    public function testSwaggerAction($path)
    {
        $operation = $this->getOperation($path, 'get');

        $responses = $operation->getResponses();
        $this->assertTrue($responses->has('201'));
        $this->assertEquals('An example resource', $responses->get('201')->getDescription());
    }

    public function swaggerActionPathsProvider()
    {
        return [['/api/swagger'], ['/api/swagger2']];
    }

    /**
     * @dataProvider implicitSwaggerActionMethodsProvider
     */
    public function testImplicitSwaggerAction($method)
    {
        $operation = $this->getOperation('/api/swagger/implicit', $method);

        $this->assertEquals([new Tag('implicit')], $operation->getTags());

        $responses = $operation->getResponses();
        $this->assertTrue($responses->has('201'));
        $response = $responses->get('201');
        $this->assertEquals('Operation automatically detected', $response->getDescription());
        $this->assertEquals('#/definitions/User', $response->getSchema()->getRef());

        $parameters = $operation->getParameters();
        $this->assertTrue($parameters->has('foo', 'body'));
        $parameter = $parameters->get('foo', 'body');

        $this->assertEquals('This is a parameter', $parameter->getDescription());
        $this->assertEquals('#/definitions/User', $parameter->getSchema()->getItems()->getRef());
    }

    public function implicitSwaggerActionMethodsProvider()
    {
        return [['get'], ['post']];
    }

    public function testUserAction()
    {
        $operation = $this->getOperation('/api/test/{user}', 'get');

        $this->assertEquals(['https'], $operation->getSchemes());
        $this->assertEmpty($operation->getSummary());
        $this->assertEmpty($operation->getDescription());
        $this->assertNull($operation->getDeprecated());
        $this->assertTrue($operation->getResponses()->has(200));

        $parameters = $operation->getParameters();
        $this->assertTrue($parameters->has('user', 'path'));

        $parameter = $parameters->get('user', 'path');
        $this->assertTrue($parameter->getRequired());
        $this->assertEquals('string', $parameter->getType());
        $this->assertEquals('/foo/', $parameter->getPattern());
        $this->assertEmpty($parameter->getFormat());
    }

    public function testDeprecatedAction()
    {
        $operation = $this->getOperation('/api/deprecated', 'get');

        $this->assertEquals('This action is deprecated.', $operation->getSummary());
        $this->assertEquals('Please do not use this action.', $operation->getDescription());
        $this->assertTrue($operation->getDeprecated());
    }

    public function testApiPlatform()
    {
        $operation = $this->getOperation('/api/dummies', 'get');
        $operation = $this->getOperation('/api/foo', 'get');
        $operation = $this->getOperation('/api/foo', 'post');
        $operation = $this->getOperation('/api/dummies/{id}', 'get');
    }

    public function testUserModel()
    {
        $this->assertEquals(
            [
                'type' => 'object',
                'properties' => [
                    'money' => [
                        'type' => 'number',
                        'format' => 'float',
                        'default' => 0.0,
                    ],
                    'id' => [
                        'type' => 'integer',
                        'description' => 'User id',
                        'readOnly' => true,
                        'title' => 'userid',
                        'example' => 1,
                    ],
                    'email' => [
                        'type' => 'string',
                        'readOnly' => false,
                    ],
                    'roles' => [
                        'title' => 'roles',
                        'type' => 'array',
                        'description' => 'User roles',
                        'example' => '["ADMIN","SUPERUSER"]',
                        'items' => ['type' => 'string'],
                        'default' => ['user'],
                    ],
                    'friendsNumber' => [
                        'type' => 'string',
                    ],
                    'creationDate' => [
                        'type' => 'string',
                        'format' => 'date-time',
                    ],
                    'users' => [
                        'items' => [
                            '$ref' => '#/definitions/User',
                        ],
                        'type' => 'array',
                    ],
                    'friend' => [
                        '$ref' => '#/definitions/User',
                    ],
                    'dummy' => [
                        '$ref' => '#/definitions/Dummy2',
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['disabled', 'enabled'],
                    ],
                    'dateAsInterface' => [
                        'type' => 'string',
                        'format' => 'date-time',
                    ],
                ],
            ],
            $this->getModel('User')->toArray()
        );
    }

    public function testFormSupport()
    {
        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'strings' => [
                    'items' => ['type' => 'string'],
                    'type' => 'array',
                ],
                'dummy' => ['$ref' => '#/definitions/DummyType'],
                'dummies' => [
                    'items' => ['$ref' => '#/definitions/DummyType'],
                    'type' => 'array',
                ],
                'empty_dummies' => [
                    'items' => ['$ref' => '#/definitions/DummyEmptyType'],
                    'type' => 'array',
                ],
                'quz' => [
                    'type' => 'string',
                    'description' => 'User type.',
                ],
                'entity' => [
                    'type' => 'string',
                    'format' => 'Entity id',
                ],
                'entities' => [
                    'type' => 'array',
                    'format' => '[Entity id]',
                    'items' => ['type' => 'string'],
                ],
                'document' => [
                    'type' => 'string',
                    'format' => 'Document id',
                ],
                'documents' => [
                    'type' => 'array',
                    'format' => '[Document id]',
                    'items' => ['type' => 'string'],
                ],
                'extended_builtin' => [
                    'type' => 'string',
                    'enum' => ['foo', 'bar'],
                ],
                'save' => [
                ],
            ],
            'required' => ['dummy', 'dummies', 'entity', 'entities', 'document', 'documents', 'extended_builtin'],
        ], $this->getModel('UserType')->toArray());

        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'bar' => [
                    'type' => 'string',
                ],
                'foo' => [
                    'type' => 'string',
                    'enum' => ['male', 'female'],
                ],
                'boo' => [
                    'type' => 'boolean',
                    'enum' => [true, false],
                ],
                'foz' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                        'enum' => ['male', 'female'],
                    ],
                ],
                'baz' => [
                    'type' => 'boolean',
                ],
                'bey' => [
                    'type' => 'integer',
                ],
                'password' => [
                    'type' => 'object',
                    'required' => ['first_field', 'second'],
                    'properties' => [
                        'first_field' => [
                            'type' => 'string',
                            'format' => 'password',
                        ],
                        'second' => [
                            'type' => 'string',
                            'format' => 'password',
                        ],
                    ],
                ],
            ],
            'required' => ['foo', 'foz', 'password'],
        ], $this->getModel('DummyType')->toArray());
    }

    public function testSecurityAction()
    {
        $operation = $this->getOperation('/api/security', 'get');

        $expected = [
            ['api_key' => []],
            ['basic' => []],
        ];
        $this->assertEquals($expected, $operation->getSecurity());
    }

    public function testClassSecurityAction()
    {
        $operation = $this->getOperation('/api/security/class', 'get');

        $expected = [
            ['basic' => []],
        ];
        $this->assertEquals($expected, $operation->getSecurity());
    }

    public function testSymfonyConstraintDocumentation()
    {
        $this->assertEquals([
            'required' => [
                'propertyNotBlank',
                'propertyNotNull',
            ],
            'properties' => [
                'propertyNotBlank' => [
                    'type' => 'integer',
                ],
                'propertyNotNull' => [
                    'type' => 'integer',
                ],
                'propertyAssertLength' => [
                    'type' => 'integer',
                    'maxLength' => '50',
                    'minLength' => '0',
                ],
                'propertyRegex' => [
                    'type' => 'integer',
                    'pattern' => '.*[a-z]{2}.*',
                ],
                'propertyCount' => [
                    'type' => 'integer',
                    'maxItems' => '10',
                    'minItems' => '0',
                ],
                'propertyChoice' => [
                    'type' => 'integer',
                    'enum' => ['choice1', 'choice2'],
                ],
                'propertyChoiceWithCallback' => [
                    'type' => 'integer',
                    'enum' => ['choice1', 'choice2'],
                ],
                'propertyChoiceWithCallbackWithoutClass' => [
                    'type' => 'integer',
                    'enum' => ['choice1', 'choice2'],
                ],
                'propertyExpression' => [
                    'type' => 'integer',
                    'pattern' => 'If this is a tech post, the category should be either php or symfony!',
                ],
                'propertyRange' => [
                    'type' => 'integer',
                    'maximum' => 5,
                    'minimum' => 1,
                ],
                'propertyLessThan' => [
                    'type' => 'integer',
                    'exclusiveMaximum' => 42,
                ],
                'propertyLessThanOrEqual' => [
                    'type' => 'integer',
                    'maximum' => 23,
                ],
            ],
            'type' => 'object',
        ], $this->getModel('SymfonyConstraints')->toArray());
    }

    public function testConfigReference()
    {
        $operation = $this->getOperation('/api/configReference', 'get');
        $this->assertEquals('#/definitions/Test', $operation->getResponses()->get('200')->getSchema()->getRef());
        $this->assertEquals('#/responses/201', $operation->getResponses()->get('201')->getRef());
    }

    public function testOperationsWithOtherAnnotationsAction()
    {
        $getOperation = $this->getOperation('/api/multi-annotations', 'get');
        $this->assertSame('This is the get operation', $getOperation->getDescription());
        $this->assertSame('Worked well!', $getOperation->getResponses()->get(200)->getDescription());

        $postOperation = $this->getOperation('/api/multi-annotations', 'post');
        $this->assertSame('This is post', $postOperation->getDescription());
        $this->assertSame('Worked well!', $postOperation->getResponses()->get(200)->getDescription());
    }

    public function testNoDuplicatedParameters()
    {
        $this->assertFalse($this->getOperation('/api/article/{id}', 'get')->getParameters()->has('id', 'path'));
    }
}
