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

class FunctionalTest extends WebTestCase
{
    public function testConfiguredDocumentation()
    {
        $this->assertEquals('My Default App', $this->getOpenApiDefinition()->info->title);
        $this->assertEquals('My Test App', $this->getOpenApiDefinition('test')->info->title);
    }

    public function testUndocumentedAction()
    {
        $api = $this->getOpenApiDefinition();

        $this->assertNotHasPath('/undocumented', $api);
        $this->assertNotHasPath('/api/admin', $api);
    }

    public function testFetchArticleAction()
    {
        $operation = $this->getOperation('/api/article/{id}', 'get');

        $response = $this->getOperationResponse($operation, '200');
        $this->assertEquals('#/definitions/Article', $response->schema->ref);

        // Ensure that groups are supported
        $model = $this->getModel('Article');
        $property = $this->getProperty($model, 'author');
        $this->assertSame('#/definitions/User2', $property->ref);
        $this->assertObjectNotHasAttribute('content', $property);
    }

    public function testFilteredAction()
    {
        $api = $this->getOpenApiDefinition();

        $this->assertNotHasPath('/filtered', $api);
    }

    /**
     * Tests that the paths are automatically resolved in Swagger annotations.
     *
     * @dataProvider swaggerActionPathsProvider
     */
    public function testSwaggerAction($path)
    {
        $operation = $this->getOperation($path, 'get');
        $response = $this->getOperationResponse($operation, '201');
        $this->assertEquals('An example resource', $response->description);
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

        $this->assertEquals(['implicit'], $operation->tags);

        $response = $this->getOperationResponse($operation, '201');
        $this->assertEquals('Operation automatically detected', $response->description);
        $this->assertEquals('#/definitions/User', $response->schema->ref);

        $parameter = $this->getParameter($operation, 'foo', 'body');
        $this->assertEquals('This is a parameter', $parameter->description);
        $this->assertEquals('#/definitions/User', $parameter->schema->items->ref);
    }

    public function implicitSwaggerActionMethodsProvider()
    {
        return [['get'], ['post']];
    }

    public function testUserAction()
    {
        $operation = $this->getOperation('/api/test/{user}', 'get');

        $this->assertEquals(['https'], $operation->schemes);
        $this->assertEmpty($operation->summary);
        $this->assertEmpty($operation->description);
        $this->assertNull($operation->deprecated);
        $this->assertHasResponse('200', $operation);

        $parameter = $this->getParameter($operation, 'user', 'path');
        $this->assertTrue($parameter->required);
        $this->assertEquals('string', $parameter->type);
        $this->assertEquals('/foo/', $parameter->pattern);
        $this->assertEmpty($parameter->format);
    }

    public function testFOSRestAction()
    {
        $operation = $this->getOperation('/api/fosrest', 'post');

        $body = $this->getParameter($operation, 'body', 'body');

        $fooParameter = $this->getParameter($operation, 'foo', 'query');
        $this->assertNotNull($fooParameter->pattern);
        $this->assertEquals('\d+', $fooParameter->pattern);
        $this->assertNull($fooParameter->format);

        $barProperty = $this->getProperty($body->schema, 'bar');
        $this->assertNotNull($barProperty->pattern);
        $this->assertEquals('\d+', $barProperty->pattern);
        $this->assertNull($barProperty->format);

        $bazProperty = $this->getProperty($body->schema, 'baz');
        $this->assertNotNull($bazProperty->format);
        $this->assertEquals('IsTrue', $bazProperty->format);
        $this->assertNull($bazProperty->pattern);

        // The _format path attribute should be removed
        $this->assertNotHasParameter('_format', 'path', $operation);
    }

    public function testDeprecatedAction()
    {
        $operation = $this->getOperation('/api/deprecated', 'get');

        $this->assertEquals('This action is deprecated.', $operation->summary);
        $this->assertEquals('Please do not use this action.', $operation->description);
        $this->assertTrue($operation->deprecated);
    }

    public function testApiPlatform()
    {
        $this->getOperation('/api/dummies', 'get');
        $this->getOperation('/api/foo', 'get');
        $this->getOperation('/api/foo', 'post');
        $this->getOperation('/api/dummies/{id}', 'get');
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
                        'default' => null,
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
                ],
                'definition' => 'User',
            ],
            $this->toArray($this->getModel('User'))
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
            ],
            'required' => ['dummy', 'dummies', 'entity', 'entities', 'document', 'documents', 'extended_builtin'],
            'definition' => 'UserType',
        ], $this->toArray($this->getModel('UserType')));

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
            'definition' => 'DummyType',
        ], $this->toArray($this->getModel('DummyType')));
    }

    public function testSecurityAction()
    {
        $operation = $this->getOperation('/api/security', 'get');

        $expected = [
            ['api_key' => []],
            ['basic' => []],
        ];
        $this->assertEquals($expected, $operation->security);
    }

    public function testClassSecurityAction()
    {
        $operation = $this->getOperation('/api/security/class', 'get');

        $expected = [
            ['basic' => []],
        ];
        $this->assertEquals($expected, $operation->security);
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
            ],
            'type' => 'object',
            'definition' => 'SymfonyConstraints',
        ], $this->toArray($this->getModel('SymfonyConstraints')));
    }

    public function testConfigReference()
    {
        $operation = $this->getOperation('/api/configReference', 'get');
        $response = $this->getOperationResponse($operation, '200');
        $this->assertEquals('#/definitions/Test', $response->schema->ref);
        $response = $this->getOperationResponse($operation, '201');
        $this->assertEquals('#/responses/201', $response->ref);
    }

    public function testOperationsWithOtherAnnotationsAction()
    {
        $getOperation = $this->getOperation('/api/multi-annotations', 'get');
        $this->assertSame('This is the get operation', $getOperation->description);
        $getResponse = $this->getOperationResponse($getOperation, '200');
        $this->assertSame('Worked well!', $getResponse->description);

        $postOperation = $this->getOperation('/api/multi-annotations', 'post');
        $this->assertSame('This is post', $postOperation->description);
        $postResponse = $this->getOperationResponse($postOperation, '200');
        $this->assertSame('Worked well!', $postResponse->description);
    }
}
