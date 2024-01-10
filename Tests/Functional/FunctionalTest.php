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

use Doctrine\Common\Annotations\Reader;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\Tests\Helper;
use OpenApi\Annotations as OAAnnotations;
use OpenApi\Attributes as OAAttributes;
use OpenApi\Generator;
use Symfony\Component\Serializer\Annotation\SerializedName;
use const PHP_VERSION_ID;

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

    /**
     * @dataProvider provideArticleRoute
     */
    public function testFetchArticleAction(string $articleRoute)
    {
        $operation = $this->getOperation($articleRoute, 'get');

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

    public function provideArticleRoute(): iterable
    {
        if (interface_exists(Reader::class)) {
            yield 'Annotations' => ['/api/article/{id}'];
        }

        if (PHP_VERSION_ID >= 80100) {
            yield 'Attributes' => ['/api/article_attributes/{id}'];
        }
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
        $this->assertSame(Generator::UNDEFINED, $path->post);

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

        $this->assertInstanceOf(OAAnnotations\RequestBody::class, $operation->requestBody);
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

        $this->assertEquals(Generator::UNDEFINED, $operation->security);
        $this->assertEquals(Generator::UNDEFINED, $operation->summary);
        $this->assertEquals(Generator::UNDEFINED, $operation->description);
        $this->assertEquals(Generator::UNDEFINED, $operation->deprecated);
        $this->assertHasResponse(200, $operation);

        $this->assertHasParameter('user', 'path', $operation);
        $parameter = Util::getOperationParameter($operation, 'user', 'path');
        $this->assertTrue($parameter->required);
        $this->assertEquals('string', $parameter->schema->type);
        $this->assertEquals('/foo/', $parameter->schema->pattern);
        $this->assertEquals(Generator::UNDEFINED, $parameter->schema->format);
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
                        'oneOf' => [
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
                'required' => [
                    'id',
                    'roles',
                    'money',
                    'creationDate',
                    'users',
                    'status',
                    'dateAsInterface',
                    'dummy',
                ],
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

        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'quz' => [
                    '$ref' => '#/components/schemas/User',
                ],
            ],
            'required' => ['quz'],
            'schema' => 'FormWithModel',
        ], json_decode($this->getModel('FormWithModel')->toJson(), true));
    }

    /**
     * @dataProvider provideSecurityRoute
     */
    public function testSecurityAction(string $route)
    {
        $operation = $this->getOperation($route, 'get');

        $expected = [
            ['api_key' => []],
            ['basic' => []],
            ['oauth2' => ['scope_1']],
        ];
        $this->assertEquals($expected, $operation->security);
    }

    public function provideSecurityRoute(): iterable
    {
        yield 'Annotations' => ['/api/security'];

        if (PHP_VERSION_ID >= 80100) {
            yield 'Attributes' => ['/api/security_attributes'];
        }
    }

    /**
     * @dataProvider provideSecurityOverrideRoute
     */
    public function testSecurityOverrideAction(string $route)
    {
        $operation = $this->getOperation($route, 'get');
        $this->assertEquals([], $operation->security);
    }

    public function provideSecurityOverrideRoute(): iterable
    {
        yield 'Annotations' => ['/api/securityOverride'];

        if (PHP_VERSION_ID >= 80100) {
            yield 'Attributes' => ['/api/security_override_attributes'];
        }
    }

    public function testInlinePHP81Parameters()
    {
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('Attributes require PHP 8.1');
        }

        $operation = $this->getOperation('/api/inline_path_parameters', 'get');
        $this->assertCount(1, $operation->parameters);
        $this->assertInstanceOf(OAAttributes\PathParameter::class, $operation->parameters[0]);
        $this->assertSame($operation->parameters[0]->name, 'product_id');
        $this->assertSame($operation->parameters[0]->schema->type, 'string');
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
        if (TestKernel::isAttributesAvailable()) {
            $modelName = 'SymfonyConstraints81';
        } else {
            $modelName = 'SymfonyConstraints80';
        }

        $expected = [
            'required' => [
                'propertyNotBlank',
                'propertyNotNull',
                'propertyAssertLength',
                'propertyRegex',
                'propertyCount',
                'propertyChoice',
                'propertyChoiceWithCallback',
                'propertyChoiceWithCallbackWithoutClass',
                'propertyChoiceWithMultiple',
                'propertyExpression',
                'propertyRange',
                'propertyRangeDate',
                'propertyLessThan',
                'propertyLessThanDate',
                'propertyLessThanOrEqual',
                'propertyLessThanOrEqualDate',
                'propertyGreaterThan',
                'propertyGreaterThanDate',
                'propertyGreaterThanOrEqual',
                'propertyGreaterThanOrEqualDate',
            ],
            'properties' => [
                'propertyNotBlank' => [
                    'type' => 'integer',
                    'maxItems' => 10,
                    'minItems' => 0,
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
                'propertyRangeDate' => [
                    'type' => 'string',
                    'format' => 'date-time',
                ],
                'propertyLessThan' => [
                    'type' => 'integer',
                    'exclusiveMaximum' => true,
                    'maximum' => 42,
                ],
                'propertyLessThanDate' => [
                    'type' => 'string',
                    'format' => 'date-time',
                ],
                'propertyLessThanOrEqual' => [
                    'type' => 'integer',
                    'maximum' => 23,
                ],
                'propertyLessThanOrEqualDate' => [
                    'type' => 'string',
                    'format' => 'date-time',
                ],
                'propertyWithCompoundValidationRule' => [
                    'type' => 'integer',
                ],
                'propertyGreaterThan' => [
                    'type' => 'integer',
                    'exclusiveMinimum' => true,
                    'minimum' => 42,
                ],
                'propertyGreaterThanDate' => [
                    'type' => 'string',
                    'format' => 'date-time',
                ],
                'propertyGreaterThanOrEqual' => [
                    'type' => 'integer',
                    'minimum' => 23,
                ],
                'propertyGreaterThanOrEqualDate' => [
                    'type' => 'string',
                    'format' => 'date-time',
                ],
            ],
            'type' => 'object',
            'schema' => $modelName,
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

        $this->assertEquals($expected, json_decode($this->getModel($modelName)->toJson(), true));
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

        if (TestKernel::isAttributesAvailable()) {
            $model = $this->getModel('SerializedNameEntity');
        } else {
            $model = $this->getModel('SerializedNameEnt');
        }

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
        $this->assertEquals('get_api_nelmio_apidoc_tests_functional_api_fetcharticle', $operation->operationId);
    }

    public function testNamedRouteOperationId()
    {
        $operation = $this->getOperation('/api/named_route-operation-id', 'get');
        $this->assertEquals('get_api_named_route_operation_id', $operation->operationId);

        $operation = $this->getOperation('/api/named_route-operation-id', 'post');
        $this->assertEquals('post_api_named_route_operation_id', $operation->operationId);
    }

    public function testCustomOperationId()
    {
        $operation = $this->getOperation('/api/custom-operation-id', 'get');
        $this->assertEquals('get-custom-operation-id', $operation->operationId);

        $operation = $this->getOperation('/api/custom-operation-id', 'post');
        $this->assertEquals('post-custom-operation-id', $operation->operationId);
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
        if (TestKernel::isAttributesAvailable()) {
            $model = $this->getModel('SymfonyDiscriminator81');
        } else {
            $model = $this->getModel('SymfonyDiscriminator80');
        }

        $this->assertInstanceOf(OAAnnotations\Discriminator::class, $model->discriminator);
        $this->assertSame('type', $model->discriminator->propertyName);
        $this->assertCount(2, $model->discriminator->mapping);
        $this->assertArrayHasKey('one', $model->discriminator->mapping);
        $this->assertArrayHasKey('two', $model->discriminator->mapping);
        $this->assertNotSame(Generator::UNDEFINED, $model->oneOf);
        $this->assertCount(2, $model->oneOf);
    }

    public function testModelsWithDiscriminatorMapAreLoadedWithOpenApiPolymorphismWhenUsingFileConfiguration()
    {
        $model = $this->getModel('SymfonyDiscriminatorFileMapping');

        $this->assertInstanceOf(OAAnnotations\Discriminator::class, $model->discriminator);
        $this->assertSame('type', $model->discriminator->propertyName);
        $this->assertCount(2, $model->discriminator->mapping);
        $this->assertArrayHasKey('one', $model->discriminator->mapping);
        $this->assertArrayHasKey('two', $model->discriminator->mapping);
        $this->assertNotSame(Generator::UNDEFINED, $model->oneOf);
        $this->assertCount(2, $model->oneOf);
    }

    public function testDiscriminatorMapLoadsChildrenModels()
    {
        // get model does its own assertions
        $this->getModel('SymfonyDiscriminatorOne');
        $this->getModel('SymfonyDiscriminatorTwo');
    }

    public function testNoAdditionalPropertiesSupport()
    {
        $model = $this->getModel('AddProp');

        $this->assertFalse($model->additionalProperties);
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testEnumSupport()
    {
        $model = $this->getModel('ArticleType81');

        $this->assertSame('string', $model->type);
        $this->assertCount(2, $model->enum);

        $model = $this->getModel('ArticleType81NotBacked');

        $this->assertSame('object', $model->type, 'Non backed enums cannot be described');

        $model = $this->getModel('ArticleType81IntBacked');

        $this->assertSame('integer', $model->type);
        $this->assertCount(2, $model->enum);

        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                ],
                'type' => [
                    '$ref' => '#/components/schemas/ArticleType81',
                ],
                'intBackedType' => [
                    '$ref' => '#/components/schemas/ArticleType81IntBacked',
                ],
                'notBackedType' => [
                    '$ref' => '#/components/schemas/ArticleType81NotBacked',
                ],
                'nullableType' => [
                    'nullable' => true,
                    'allOf' => [
                        ['$ref' => '#/components/schemas/ArticleType81'],
                    ],
                ],
            ],
            'required' => ['id', 'type', 'intBackedType', 'notBackedType'],
            'schema' => 'Article81',
        ], json_decode($this->getModel('Article81')->toJson(), true));
    }

    public function testEntitiesWithOverriddenSchemaTypeDoNotReadOtherProperties()
    {
        if (TestKernel::isAttributesAvailable()) {
            $model = $this->getModel('EntityWithAlternateType81');
        } else {
            $model = $this->getModel('EntityWithAlternateType80');
        }

        $this->assertSame('array', $model->type);
        $this->assertSame('string', $model->items->type);
        $this->assertSame(Generator::UNDEFINED, $model->properties);
    }

    public function testEntitiesWithRefInSchemaDoNoReadOtherProperties()
    {
        $model = $this->getModel('EntityWithRef');

        $this->assertSame(Generator::UNDEFINED, $model->type);
        $this->assertSame('#/components/schemas/Test', $model->ref);
        $this->assertSame(Generator::UNDEFINED, $model->properties);
    }

    public function testEntitiesWithObjectTypeStillReadProperties()
    {
        $model = $this->getModel('EntityWithObjectType');

        $this->assertSame('object', $model->type);
        $this->assertCount(1, $model->properties);
        $property = Util::getProperty($model, 'notIgnored');
        $this->assertSame('string', $property->type);
    }

    public function testFormsWithOverriddenSchemaTypeDoNotReadOtherProperties()
    {
        $model = $this->getModel('FormWithAlternateSchemaType');

        $this->assertSame('string', $model->type);
        $this->assertSame(Generator::UNDEFINED, $model->properties);
    }

    public function testFormWithRefInSchemaDoNoReadOtherProperties()
    {
        $model = $this->getModel('FormWithRefType');

        $this->assertSame(Generator::UNDEFINED, $model->type);
        $this->assertSame('#/components/schemas/Test', $model->ref);
        $this->assertSame(Generator::UNDEFINED, $model->properties);
    }

    public function testFormCsrfIsOnlyDetectedIfCsrfExtensionIsEnabled(): void
    {
        // Make sure that test precondition is correct.
        $isCsrfFormExtensionEnabled = self::getContainer()->getParameter('form.type_extension.csrf.enabled');
        $this->assertFalse($isCsrfFormExtensionEnabled, 'The test needs the csrf form extension to be disabled.');

        $model = $this->getModel('FormWithCsrfProtectionEnabledType');

        // Make sure that no token property was added
        $this->assertCount(1, $model->properties);
        $this->assertHasProperty('name', $model);
    }

    public function testEntityWithNullableSchemaSet()
    {
        $model = $this->getModel('EntityWithNullableSchemaSet');

        $this->assertCount(6, $model->properties);

        // nullablePropertyNullableNotSet
        $this->assertTrue($model->properties[0]->nullable);

        // nullablePropertyNullableFalseSet
        $this->assertSame(Generator::UNDEFINED, $model->properties[1]->nullable);

        // nullablePropertyNullableTrueSet
        $this->assertTrue($model->properties[2]->nullable);

        // nonNullablePropertyNullableNotSet
        $this->assertSame(Generator::UNDEFINED, $model->properties[3]->nullable);

        // nonNullablePropertyNullableFalseSet
        $this->assertSame(Generator::UNDEFINED, $model->properties[4]->nullable);

        // nonNullablePropertyNullableTrueSet
        $this->assertTrue($model->properties[5]->nullable);
    }

    public function testContextPassedToNameConverter()
    {
        $operation = $this->getOperation('/api/name_converter_context', 'get');

        $response = $this->getOperationResponse($operation, '200');
        self::assertEquals([
            'response' => '200',
            'description' => '',
            'content' => [
                'application/json' => [
                    'schema' => ['$ref' => '#/components/schemas/EntityThroughNameConverter'],
                ],
            ],
        ], json_decode($response->toJson(), true));

        $model = $this->getModel('EntityThroughNameConverter');
        $this->assertCount(2, $model->properties);
        $this->assertNotHasProperty('id', $model);
        $this->assertHasProperty('name_converter_context_id', $model);
        $this->assertNotHasProperty('name', $model);
        $this->assertHasProperty('name_converter_context_name', $model);

        $response = $this->getOperationResponse($operation, '201');
        self::assertEquals([
            'response' => '201',
            'description' => 'Same class without context',
            'content' => [
                'application/json' => [
                    'schema' => ['$ref' => '#/components/schemas/EntityThroughNameConverter2'],
                ],
            ],
        ], json_decode($response->toJson(), true));

        $model = $this->getModel('EntityThroughNameConverter2');
        $this->assertCount(2, $model->properties);
        $this->assertNotHasProperty('name_converter_context_id', $model);
        $this->assertHasProperty('id', $model);
        $this->assertNotHasProperty('name_converter_context_name', $model);
        $this->assertHasProperty('name', $model);
    }
}
