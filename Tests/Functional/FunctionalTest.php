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

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\Tests\Helper;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\SerializedName;

class FunctionalTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);
    }

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

        $this->assertHasResponse('200', $operation);
        $response = $this->getOperationResponse($operation, '200');
        $this->assertEquals('#/components/schemas/Article', $response->content['application/json']->schema->ref);

        // Ensure that groups are supported
        $articleModel = $this->getModel('Article');
        $this->assertCount(1, $articleModel->properties);
        $this->assertHasProperty('author', $articleModel);
        $this->assertSame('#/components/schemas/User2', Util::getProperty($articleModel, 'author')->ref);
        $this->assertNotHasProperty('author', Util::getProperty($articleModel, 'author'));
    }

    public function testFilteredAction()
    {
        $openApi = $this->getOpenApiDefinition();

        $this->assertNotHasPath('/filtered', $openApi);
    }

    /**
     * Tests that the paths are automatically resolved in Swagger annotations.
     *
     * @dataProvider swaggerActionPathsProvider
     */
    public function testSwaggerAction(string $path)
    {
        $operation = $this->getOperation($path, 'get');

        $this->assertHasResponse('201', $operation);
        $response = $this->getOperationResponse($operation, '201');
        $this->assertEquals('An example resource', $response->description);
    }

    public function swaggerActionPathsProvider()
    {
        return [['/api/swagger'], ['/api/swagger2']];
    }

    public function testAnnotationWithManualPath()
    {
        $path = $this->getPath('/api/swagger2');
        $this->assertSame(OA\UNDEFINED, $path->post);

        $operation = $this->getOperation('/api/swagger', 'get');
        $this->assertNotHasParameter('Accept-Version', 'header', $operation);

        $operation = $this->getOperation('/api/swagger2', 'get');
        $this->assertHasParameter('Accept-Version', 'header', $operation);
    }

    /**
     * @dataProvider implicitSwaggerActionMethodsProvider
     */
    public function testImplicitSwaggerAction(string $method)
    {
        $operation = $this->getOperation('/api/swagger/implicit', $method);

        $this->assertEquals(['implicit'], $operation->tags);

        $this->assertHasResponse('201', $operation);
        $response = $this->getOperationResponse($operation, '201');
        $this->assertEquals('Operation automatically detected', $response->description);
        $this->assertEquals('#/components/schemas/User', $response->content['application/json']->schema->ref);

        $this->assertInstanceOf(OA\RequestBody::class, $operation->requestBody);
        $requestBody = $operation->requestBody;
        $this->assertEquals('This is a request body', $requestBody->description);
        $this->assertEquals('array', $requestBody->content['application/json']->schema->type);
        $this->assertEquals('#/components/schemas/User', $requestBody->content['application/json']->schema->items->ref);
    }

    public function implicitSwaggerActionMethodsProvider()
    {
        return [['get'], ['post']];
    }

    public function testUserAction()
    {
        $operation = $this->getOperation('/api/test/{user}', 'get');

        $this->assertEquals(OA\UNDEFINED, $operation->security);
        $this->assertEquals(OA\UNDEFINED, $operation->summary);
        $this->assertEquals(OA\UNDEFINED, $operation->description);
        $this->assertEquals(OA\UNDEFINED, $operation->deprecated);
        $this->assertHasResponse(200, $operation);

        $this->assertHasParameter('user', 'path', $operation);
        $parameter = Util::getOperationParameter($operation, 'user', 'path');
        $this->assertTrue($parameter->required);
        $this->assertEquals('string', $parameter->schema->type);
        $this->assertEquals('/foo/', $parameter->schema->pattern);
        $this->assertEquals(OA\UNDEFINED, $parameter->schema->format);
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
                    'location' => [
                        'title' => 'User Location.',
                        'type' => 'string',
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
                            '$ref' => '#/components/schemas/User',
                        ],
                        'type' => 'array',
                    ],
                    'friend' => [
                        'nullable' => true,
                        'allOf' => [
                            ['$ref' => '#/components/schemas/User'],
                        ],
                    ],
                    'friends' => [
                        'nullable' => true,
                        'items' => [
                            '$ref' => '#/components/schemas/User',
                        ],
                        'type' => 'array',
                    ],
                    'dummy' => [
                        '$ref' => '#/components/schemas/Dummy2',
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
                'schema' => 'User',
            ],
            json_decode($this->getModel('User')->toJson(), true)
        );
    }

    public function testFormSupport()
    {
        $this->assertEquals([
            'type' => 'object',
            'description' => 'this is the description of an user',
            'properties' => [
                'strings' => [
                    'items' => ['type' => 'string'],
                    'type' => 'array',
                ],
                'dummy' => ['$ref' => '#/components/schemas/DummyType'],
                'dummies' => [
                    'items' => ['$ref' => '#/components/schemas/DummyType'],
                    'type' => 'array',
                ],
                'empty_dummies' => [
                    'items' => ['$ref' => '#/components/schemas/DummyEmptyType'],
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
            'schema' => 'UserType',
        ], json_decode($this->getModel('UserType')->toJson(), true));

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
            'schema' => 'DummyType',
        ], json_decode($this->getModel('DummyType')->toJson(), true));
    }

    public function testSecurityAction()
    {
        $operation = $this->getOperation('/api/security', 'get');

        $expected = [
            ['api_key' => []],
            ['basic' => []],
            ['oauth2' => ['scope_1']],
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
        $expected = [
            'required' => [
                'propertyNotBlank',
                'propertyNotNull',
            ],
            'properties' => [
                'propertyNotBlank' => [
                    'type' => 'integer',
                    'maxItems' => '10',
                    'minItems' => '0',
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
                'propertyChoiceWithMultiple' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                        'enum' => ['choice1', 'choice2'],
                    ],
                ],
                'propertyExpression' => [
                    'type' => 'integer',
                ],
                'propertyRange' => [
                    'type' => 'integer',
                    'maximum' => 5,
                    'minimum' => 1,
                ],
                'propertyLessThan' => [
                    'type' => 'integer',
                    'exclusiveMaximum' => true,
                    'maximum' => 42,
                ],
                'propertyLessThanOrEqual' => [
                    'type' => 'integer',
                    'maximum' => 23,
                ],
                'propertyWithCompoundValidationRule' => [
                    'type' => 'integer',
                ],
            ],
            'type' => 'object',
            'schema' => 'SymfonyConstraints',
        ];

        if (Helper::isCompoundValidatorConstraintSupported()) {
            $expected['required'][] = 'propertyWithCompoundValidationRule';
            $expected['properties']['propertyWithCompoundValidationRule'] = [
                'type' => 'integer',
                'maximum' => 5,
                'exclusiveMaximum' => true,
                'minimum' => 0,
                'exclusiveMinimum' => true,
            ];
        }

        $this->assertEquals($expected, json_decode($this->getModel('SymfonyConstraints')->toJson(), true));
    }

    public function testConfigReference()
    {
        $operation = $this->getOperation('/api/configReference', 'get');
        $this->assertEquals('#/components/schemas/Test', $this->getOperationResponse($operation, '200')->ref);
        $this->assertEquals('#/components/responses/201', $this->getOperationResponse($operation, '201')->ref);
    }

    public function testOperationsWithOtherAnnotationsAction()
    {
        $getOperation = $this->getOperation('/api/multi-annotations', 'get');
        $this->assertSame('This is the get operation', $getOperation->description);
        $this->assertSame('Worked well!', $this->getOperationResponse($getOperation, 200)->description);

        $postOperation = $this->getOperation('/api/multi-annotations', 'post');
        $this->assertSame('This is post', $postOperation->description);
        $this->assertSame('Worked well!', $this->getOperationResponse($postOperation, 200)->description);
    }

    public function testNoDuplicatedParameters()
    {
        $this->assertHasPath('/api/article/{id}', $this->getOpenApiDefinition());
        $this->assertNotHasParameter('id', 'path', $this->getOperation('/api/article/{id}', 'get'));
    }

    public function testSerializedNameAction()
    {
        if (!class_exists(SerializedName::class)) {
            $this->markTestSkipped('Annotation @SerializedName doesn\'t exist.');
        }

        $model = $this->getModel('SerializedNameEnt');
        $this->assertCount(2, $model->properties);

        $this->assertNotHasProperty('foo', $model);
        $this->assertHasProperty('notfoo', $model);

        $this->assertNotHasProperty('bar', $model);
        $this->assertHasProperty('notwhatyouthink', $model);
    }

    public function testCompoundEntityAction()
    {
        $model = $this->getModel('CompoundEntity');
        $this->assertCount(1, $model->properties);

        $this->assertHasProperty('complex', $model);

        $property = $model->properties[0];
        $this->assertCount(2, $property->oneOf);

        $this->assertSame('integer', $property->oneOf[0]->type);
        $this->assertSame('array', $property->oneOf[1]->type);
        $this->assertSame('#/components/schemas/CompoundEntity', $property->oneOf[1]->items->ref);
    }

    public function testInvokableController()
    {
        $operation = $this->getOperation('/api/invoke', 'get');
        $this->assertSame('Invokable!', $this->getOperationResponse($operation, 200)->description);
    }

    public function testDefaultOperationId()
    {
        $operation = $this->getOperation('/api/article/{id}', 'get');
        $this->assertNull($operation->operationId);
    }

    /**
     * Related to https://github.com/nelmio/NelmioApiDocBundle/issues/1756
     * Ensures private/protected properties are not exposed, just like the symfony serializer does.
     */
    public function testPrivateProtectedExposure()
    {
        // Ensure that groups are supported
        $model = $this->getModel('PrivateProtectedExposure');
        $this->assertCount(1, $model->properties);
        $this->assertHasProperty('publicField', $model);
        $this->assertNotHasProperty('privateField', $model);
        $this->assertNotHasProperty('protectedField', $model);
        $this->assertNotHasProperty('protected', $model);
    }

    public function testModelsWithDiscriminatorMapAreLoadedWithOpenApiPolymorphism()
    {
        $model = $this->getModel('SymfonyDiscriminator');

        $this->assertInstanceOf(OA\Discriminator::class, $model->discriminator);
        $this->assertSame('type', $model->discriminator->propertyName);
        $this->assertCount(2, $model->discriminator->mapping);
        $this->assertArrayHasKey('one', $model->discriminator->mapping);
        $this->assertArrayHasKey('two', $model->discriminator->mapping);
        $this->assertNotSame(OA\UNDEFINED, $model->oneOf);
        $this->assertCount(2, $model->oneOf);
    }

    public function testDiscriminatorMapLoadsChildrenModels()
    {
        // get model does its own assertions
        $this->getModel('SymfonyDiscriminatorOne');
        $this->getModel('SymfonyDiscriminatorTwo');
    }
}
