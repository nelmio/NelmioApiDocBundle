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
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Serializer\Annotation\SerializedName;

class FunctionalTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);
    }

    public function testConfiguredDocumentation(): void
    {
        self::assertEquals('My Default App', $this->getOpenApiDefinition()->info->title);
        self::assertEquals(['buildHash' => 'ab1234567890'], $this->getOpenApiDefinition()->info->x);
        self::assertEquals('My Test App', $this->getOpenApiDefinition('test')->info->title);
    }

    public function testUndocumentedAction(): void
    {
        $api = $this->getOpenApiDefinition();

        $this->assertNotHasPath('/undocumented', $api);
        $this->assertNotHasPath('/api/admin', $api);
    }

    /**
     * @dataProvider provideArticleRoute
     */
    public function testFetchArticleAction(string $articleRoute): void
    {
        $operation = $this->getOperation($articleRoute, 'get');

        $this->assertHasResponse('200', $operation);
        $response = $this->getOperationResponse($operation, '200');
        self::assertEquals('#/components/schemas/Article', $response->content['application/json']->schema->ref);

        // Ensure that groups are supported
        $articleModel = $this->getModel('Article');
        self::assertCount(1, $articleModel->properties);
        $this->assertHasProperty('author', $articleModel);
        self::assertSame('#/components/schemas/User2', Util::getProperty($articleModel, 'author')->ref);
        $this->assertNotHasProperty('author', Util::getProperty($articleModel, 'author'));
    }

    public static function provideArticleRoute(): \Generator
    {
        if (interface_exists(Reader::class)) {
            yield 'Annotations' => ['/api/article/{id}'];
        }

        if (\PHP_VERSION_ID >= 80100) {
            yield 'Attributes' => ['/api/article_attributes/{id}'];
        }
    }

    public function testFilteredAction(): void
    {
        $openApi = $this->getOpenApiDefinition();

        $this->assertNotHasPath('/filtered', $openApi);
    }

    /**
     * Tests that the paths are automatically resolved in Swagger annotations.
     *
     * @dataProvider swaggerActionPathsProvider
     */
    public function testSwaggerAction(string $path): void
    {
        $operation = $this->getOperation($path, 'get');

        $this->assertHasResponse('201', $operation);
        $response = $this->getOperationResponse($operation, '201');
        self::assertEquals('An example resource', $response->description);
    }

    public static function swaggerActionPathsProvider(): \Generator
    {
        yield ['/api/swagger'];

        yield ['/api/swagger2'];
    }

    public function testAnnotationWithManualPath(): void
    {
        $path = $this->getPath('/api/swagger2');
        self::assertSame(Generator::UNDEFINED, $path->post);

        $operation = $this->getOperation('/api/swagger', 'get');
        $this->assertNotHasParameter('Accept-Version', 'header', $operation);

        $operation = $this->getOperation('/api/swagger2', 'get');
        $this->assertHasParameter('Accept-Version', 'header', $operation);
    }

    /**
     * @dataProvider implicitSwaggerActionMethodsProvider
     */
    public function testImplicitSwaggerAction(string $method): void
    {
        $operation = $this->getOperation('/api/swagger/implicit', $method);

        self::assertEquals(['implicit'], $operation->tags);

        $this->assertHasResponse('201', $operation);
        $response = $this->getOperationResponse($operation, '201');
        self::assertEquals('Operation automatically detected', $response->description);
        self::assertEquals('#/components/schemas/User', $response->content['application/json']->schema->ref);

        self::assertInstanceOf(OAAnnotations\RequestBody::class, $operation->requestBody);
        $requestBody = $operation->requestBody;
        self::assertEquals('This is a request body', $requestBody->description);
        self::assertEquals('array', $requestBody->content['application/json']->schema->type);
        self::assertEquals('#/components/schemas/User', $requestBody->content['application/json']->schema->items->ref);
    }

    public static function implicitSwaggerActionMethodsProvider(): \Generator
    {
        yield ['get'];

        yield ['post'];
    }

    public function testUserAction(): void
    {
        $operation = $this->getOperation('/api/test/{user}', 'get');

        self::assertEquals(Generator::UNDEFINED, $operation->security);
        self::assertEquals(Generator::UNDEFINED, $operation->summary);
        self::assertEquals(Generator::UNDEFINED, $operation->description);
        self::assertEquals(Generator::UNDEFINED, $operation->deprecated);
        $this->assertHasResponse(200, $operation);

        $this->assertHasParameter('user', 'path', $operation);
        $parameter = Util::getOperationParameter($operation, 'user', 'path');
        self::assertTrue($parameter->required);
        self::assertEquals('string', $parameter->schema->type);
        self::assertEquals('/foo/', $parameter->schema->pattern);
        self::assertEquals(Generator::UNDEFINED, $parameter->schema->format);
    }

    public function testDeprecatedAction(): void
    {
        $operation = $this->getOperation('/api/deprecated', 'get');

        self::assertEquals('This action is deprecated.', $operation->summary);
        self::assertEquals('Please do not use this action.', $operation->description);
        self::assertTrue($operation->deprecated);
    }

    public function testApiPlatform(): void
    {
        $operation = $this->getOperation('/api/dummies', 'get');
        $operation = $this->getOperation('/api/foo', 'get');
        $operation = $this->getOperation('/api/foo', 'post');
        $operation = $this->getOperation('/api/dummies/{id}', 'get');
    }

    public function testUserModel(): void
    {
        self::assertEquals(
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

    public function testFormSupport(): void
    {
        self::assertEquals([
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

        self::assertEquals([
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

        self::assertEquals([
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
    public function testSecurityAction(string $route): void
    {
        $operation = $this->getOperation($route, 'get');

        $expected = [
            ['api_key' => []],
            ['basic' => []],
            ['oauth2' => ['scope_1']],
        ];
        self::assertEquals($expected, $operation->security);
    }

    public static function provideSecurityRoute(): \Generator
    {
        yield 'Annotations' => ['/api/security'];

        if (\PHP_VERSION_ID >= 80100) {
            yield 'Attributes' => ['/api/security_attributes'];
        }
    }

    /**
     * @dataProvider provideSecurityOverrideRoute
     */
    public function testSecurityOverrideAction(string $route): void
    {
        $operation = $this->getOperation($route, 'get');
        self::assertEquals([], $operation->security);
    }

    public static function provideSecurityOverrideRoute(): \Generator
    {
        yield 'Annotations' => ['/api/securityOverride'];

        if (\PHP_VERSION_ID >= 80100) {
            yield 'Attributes' => ['/api/security_override_attributes'];
        }
    }

    public function testInlinePHP81Parameters(): void
    {
        if (\PHP_VERSION_ID < 80100) {
            self::markTestSkipped('Attributes require PHP 8.1');
        }

        $operation = $this->getOperation('/api/inline_path_parameters', 'get');
        self::assertCount(1, $operation->parameters);
        self::assertInstanceOf(OAAttributes\PathParameter::class, $operation->parameters[0]);
        self::assertSame($operation->parameters[0]->name, 'product_id');
        self::assertSame($operation->parameters[0]->schema->type, 'string');
    }

    public function testClassSecurityAction(): void
    {
        $operation = $this->getOperation('/api/security/class', 'get');

        $expected = [
            ['basic' => []],
        ];
        self::assertEquals($expected, $operation->security);
    }

    public function testSymfonyConstraintDocumentation(): void
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

        self::assertEquals($expected, json_decode($this->getModel($modelName)->toJson(), true));
    }

    public function testConfigReference(): void
    {
        $operation = $this->getOperation('/api/configReference', 'get');
        self::assertEquals('#/components/schemas/Test', $this->getOperationResponse($operation, '200')->ref);
        self::assertEquals('#/components/responses/201', $this->getOperationResponse($operation, '201')->ref);
    }

    public function testOperationsWithOtherAnnotationsAction(): void
    {
        $getOperation = $this->getOperation('/api/multi-annotations', 'get');
        self::assertSame('This is the get operation', $getOperation->description);
        self::assertSame('Worked well!', $this->getOperationResponse($getOperation, 200)->description);

        $postOperation = $this->getOperation('/api/multi-annotations', 'post');
        self::assertSame('This is post', $postOperation->description);
        self::assertSame('Worked well!', $this->getOperationResponse($postOperation, 200)->description);
    }

    public function testNoDuplicatedParameters(): void
    {
        $this->assertHasPath('/api/article/{id}', $this->getOpenApiDefinition());
        $this->assertNotHasParameter('id', 'path', $this->getOperation('/api/article/{id}', 'get'));
    }

    public function testSerializedNameAction(): void
    {
        if (!class_exists(SerializedName::class)) {
            self::markTestSkipped('Annotation @SerializedName doesn\'t exist.');
        }

        if (TestKernel::isAttributesAvailable()) {
            $model = $this->getModel('SerializedNameEntity');
        } else {
            $model = $this->getModel('SerializedNameEnt');
        }

        self::assertCount(2, $model->properties);

        $this->assertNotHasProperty('foo', $model);
        $this->assertHasProperty('notfoo', $model);

        $this->assertNotHasProperty('bar', $model);
        $this->assertHasProperty('notwhatyouthink', $model);
    }

    public function testCompoundEntityAction(): void
    {
        self::assertEquals([
            'schema' => 'CompoundEntity',
            'type' => 'object',
            'required' => ['complex', 'arrayOfArrayComplex'],
            'properties' => [
                'complex' => [
                    'oneOf' => [
                        [
                            'type' => 'integer',
                        ],
                        [
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/components/schemas/CompoundEntity',
                            ],
                        ],
                    ],
                ],
                'nullableComplex' => [
                    'nullable' => true,
                    'oneOf' => [
                        [
                            'type' => 'integer',
                            'nullable' => true,
                        ],
                        [
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/components/schemas/CompoundEntity',
                            ],
                        ],
                    ],
                ],
                'complexNested' => [
                    'nullable' => true,
                    'oneOf' => [
                        [
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/components/schemas/CompoundEntityNested',
                            ],
                            'nullable' => true,
                        ],
                        [
                            'type' => 'string',
                        ],
                    ],
                ],
                'arrayOfArrayComplex' => [
                    'oneOf' => [
                        [
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/components/schemas/CompoundEntityNested',
                            ],
                        ],
                        [
                            'type' => 'array',
                            'items' => [
                                'type' => 'array',
                                'items' => [
                                    '$ref' => '#/components/schemas/CompoundEntityNested',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], json_decode($this->getModel('CompoundEntity')->toJson(), true));

        self::assertEquals([
            'schema' => 'CompoundEntityNested',
            'type' => 'object',
            'required' => ['complex'],
            'properties' => [
                'complex' => [
                    'oneOf' => [
                        [
                            'type' => 'integer',
                        ],
                        [
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/components/schemas/CompoundEntity',
                            ],
                        ],
                    ],
                ],
                'nullableComplex' => [
                    'nullable' => true,
                    'oneOf' => [
                        [
                            'type' => 'integer',
                            'nullable' => true,
                        ],
                        [
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/components/schemas/CompoundEntity',
                            ],
                        ],
                    ],
                ],
                'complexNested' => [
                    'nullable' => true,
                    'oneOf' => [
                        [
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/components/schemas/CompoundEntityNested',
                            ],
                            'nullable' => true,
                        ],
                        [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ], json_decode($this->getModel('CompoundEntityNested')->toJson(), true));
    }

    public function testInvokableController(): void
    {
        $operation = $this->getOperation('/api/invoke', 'get');
        self::assertSame('Invokable!', $this->getOperationResponse($operation, 200)->description);
    }

    public function testDefaultOperationId(): void
    {
        $operation = $this->getOperation('/api/article/{id}', 'get');
        self::assertEquals('get_api_nelmio_apidoc_tests_functional_api_fetcharticle', $operation->operationId);
    }

    public function testNamedRouteOperationId(): void
    {
        $operation = $this->getOperation('/api/named_route-operation-id', 'get');
        self::assertEquals('get_api_named_route_operation_id', $operation->operationId);

        $operation = $this->getOperation('/api/named_route-operation-id', 'post');
        self::assertEquals('post_api_named_route_operation_id', $operation->operationId);
    }

    public function testCustomOperationId(): void
    {
        $operation = $this->getOperation('/api/custom-operation-id', 'get');
        self::assertEquals('get-custom-operation-id', $operation->operationId);

        $operation = $this->getOperation('/api/custom-operation-id', 'post');
        self::assertEquals('post-custom-operation-id', $operation->operationId);
    }

    /**
     * Related to https://github.com/nelmio/NelmioApiDocBundle/issues/1756
     * Ensures private/protected properties are not exposed, just like the symfony serializer does.
     */
    public function testPrivateProtectedExposure(): void
    {
        // Ensure that groups are supported
        $model = $this->getModel('PrivateProtectedExposure');
        self::assertCount(1, $model->properties);
        $this->assertHasProperty('publicField', $model);
        $this->assertNotHasProperty('privateField', $model);
        $this->assertNotHasProperty('protectedField', $model);
        $this->assertNotHasProperty('protected', $model);
    }

    public function testModelsWithDiscriminatorMapAreLoadedWithOpenApiPolymorphism(): void
    {
        if (TestKernel::isAttributesAvailable()) {
            $model = $this->getModel('SymfonyDiscriminator81');
        } else {
            $model = $this->getModel('SymfonyDiscriminator80');
        }

        self::assertInstanceOf(OAAnnotations\Discriminator::class, $model->discriminator);
        self::assertSame('type', $model->discriminator->propertyName);
        self::assertCount(2, $model->discriminator->mapping);
        self::assertArrayHasKey('one', $model->discriminator->mapping);
        self::assertArrayHasKey('two', $model->discriminator->mapping);
        self::assertNotSame(Generator::UNDEFINED, $model->oneOf);
        self::assertCount(2, $model->oneOf);
    }

    public function testModelsWithDiscriminatorMapAreLoadedWithOpenApiPolymorphismWhenUsingFileConfiguration(): void
    {
        $model = $this->getModel('SymfonyDiscriminatorFileMapping');

        self::assertInstanceOf(OAAnnotations\Discriminator::class, $model->discriminator);
        self::assertSame('type', $model->discriminator->propertyName);
        self::assertCount(2, $model->discriminator->mapping);
        self::assertArrayHasKey('one', $model->discriminator->mapping);
        self::assertArrayHasKey('two', $model->discriminator->mapping);
        self::assertNotSame(Generator::UNDEFINED, $model->oneOf);
        self::assertCount(2, $model->oneOf);
    }

    public function testDiscriminatorMapLoadsChildrenModels(): void
    {
        // get model does its own assertions
        $this->getModel('SymfonyDiscriminatorOne');
        $this->getModel('SymfonyDiscriminatorTwo');
    }

    public function testNoAdditionalPropertiesSupport(): void
    {
        $model = $this->getModel('AddProp');

        self::assertFalse($model->additionalProperties);
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testEnumSupport(): void
    {
        $model = $this->getModel('ArticleType81');

        self::assertSame('string', $model->type);
        self::assertCount(2, $model->enum);

        $model = $this->getModel('ArticleType81NotBacked');

        self::assertSame('object', $model->type, 'Non backed enums cannot be described');

        $model = $this->getModel('ArticleType81IntBacked');

        self::assertSame('integer', $model->type);
        self::assertCount(2, $model->enum);

        self::assertEquals([
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
                    'oneOf' => [
                        ['$ref' => '#/components/schemas/ArticleType81'],
                    ],
                ],
            ],
            'required' => ['id', 'type', 'intBackedType', 'notBackedType'],
            'schema' => 'Article81',
        ], json_decode($this->getModel('Article81')->toJson(), true));
    }

    public function testEntitiesWithOverriddenSchemaTypeDoNotReadOtherProperties(): void
    {
        if (TestKernel::isAttributesAvailable()) {
            $model = $this->getModel('EntityWithAlternateType81');
        } else {
            $model = $this->getModel('EntityWithAlternateType80');
        }

        self::assertSame('array', $model->type);
        self::assertSame('string', $model->items->type);
        self::assertSame(Generator::UNDEFINED, $model->properties);
    }

    public function testEntitiesWithRefInSchemaDoNoReadOtherProperties(): void
    {
        $model = $this->getModel('EntityWithRef');

        self::assertSame(Generator::UNDEFINED, $model->type);
        self::assertSame('#/components/schemas/Test', $model->ref);
        self::assertSame(Generator::UNDEFINED, $model->properties);
    }

    public function testEntitiesWithObjectTypeStillReadProperties(): void
    {
        $model = $this->getModel('EntityWithObjectType');

        self::assertSame('object', $model->type);
        self::assertCount(1, $model->properties);
        $property = Util::getProperty($model, 'notIgnored');
        self::assertSame('string', $property->type);
    }

    public function testFormsWithOverriddenSchemaTypeDoNotReadOtherProperties(): void
    {
        $model = $this->getModel('FormWithAlternateSchemaType');

        self::assertSame('string', $model->type);
        self::assertSame(Generator::UNDEFINED, $model->properties);
    }

    public function testFormWithRefInSchemaDoNoReadOtherProperties(): void
    {
        $model = $this->getModel('FormWithRefType');

        self::assertSame(Generator::UNDEFINED, $model->type);
        self::assertSame('#/components/schemas/Test', $model->ref);
        self::assertSame(Generator::UNDEFINED, $model->properties);
    }

    public function testFormCsrfIsOnlyDetectedIfCsrfExtensionIsEnabled(): void
    {
        // Make sure that test precondition is correct.
        $isCsrfFormExtensionEnabled = self::getContainer()->getParameter('form.type_extension.csrf.enabled');
        self::assertFalse($isCsrfFormExtensionEnabled, 'The test needs the csrf form extension to be disabled.');

        $model = $this->getModel('FormWithCsrfProtectionEnabledType');

        // Make sure that no token property was added
        self::assertCount(1, $model->properties);
        $this->assertHasProperty('name', $model);
    }

    public function testEntityWithNullableSchemaSet(): void
    {
        $model = $this->getModel('EntityWithNullableSchemaSet');

        self::assertCount(6, $model->properties);

        // nullablePropertyNullableNotSet
        self::assertTrue($model->properties[0]->nullable);

        // nullablePropertyNullableFalseSet
        self::assertSame(Generator::UNDEFINED, $model->properties[1]->nullable);

        // nullablePropertyNullableTrueSet
        self::assertTrue($model->properties[2]->nullable);

        // nonNullablePropertyNullableNotSet
        self::assertSame(Generator::UNDEFINED, $model->properties[3]->nullable);

        // nonNullablePropertyNullableFalseSet
        self::assertSame(Generator::UNDEFINED, $model->properties[4]->nullable);

        // nonNullablePropertyNullableTrueSet
        self::assertTrue($model->properties[5]->nullable);
    }

    public function testContextPassedToNameConverter(): void
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

        self::assertEquals([
            'schema' => 'EntityThroughNameConverter',
            'type' => 'object',
            'required' => [
                'name_converter_context_id',
                'name_converter_context_name',
                'name_converter_context_nested',
            ],
            'properties' => [
                'name_converter_context_id' => [
                    'type' => 'integer',
                ],
                'name_converter_context_name' => [
                    'type' => 'string',
                ],
                'name_converter_context_nested' => [
                    '$ref' => '#/components/schemas/EntityThroughNameConverterNested',
                ],
            ],
        ], json_decode($this->getModel('EntityThroughNameConverter')->toJson(), true));

        self::assertEquals([
            'schema' => 'EntityThroughNameConverterNested',
            'type' => 'object',
            'required' => [
                'name_converter_context_someNestedId',
                'name_converter_context_someNestedName',
            ],
            'properties' => [
                'name_converter_context_someNestedId' => [
                    'type' => 'integer',
                ],
                'name_converter_context_someNestedName' => [
                    'type' => 'string',
                ],
            ],
        ], json_decode($this->getModel('EntityThroughNameConverterNested')->toJson(), true));

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

        self::assertEquals([
            'schema' => 'EntityThroughNameConverter2',
            'type' => 'object',
            'required' => [
                'id',
                'name',
                'nested',
            ],
            'properties' => [
                'id' => [
                    'type' => 'integer',
                ],
                'name' => [
                    'type' => 'string',
                ],
                'nested' => [
                    '$ref' => '#/components/schemas/EntityThroughNameConverterNested2',
                ],
            ],
        ], json_decode($this->getModel('EntityThroughNameConverter2')->toJson(), true));

        self::assertEquals([
            'schema' => 'EntityThroughNameConverterNested2',
            'type' => 'object',
            'required' => [
                'someNestedId',
                'someNestedName',
            ],
            'properties' => [
                'someNestedId' => [
                    'type' => 'integer',
                ],
                'someNestedName' => [
                    'type' => 'string',
                ],
            ],
        ], json_decode($this->getModel('EntityThroughNameConverterNested2')->toJson(), true));
    }

    public function testArbitraryArrayModel(): void
    {
        $this->getOperation('/api/arbitrary_array', 'get');

        self::assertEquals([
            'schema' => 'Foo',
            'required' => ['articles', 'bars'],
            'properties' => [
                'articles' => [
                    'type' => 'string',
                ],
                'bars' => [
                    'type' => 'array',
                    'items' => ['$ref' => '#/components/schemas/Bar'],
                ],
            ],
            'type' => 'object',
        ], json_decode($this->getModel('Foo')->toJson(), true));

        self::assertEquals([
            'schema' => 'Bar',
            'required' => ['things', 'moreThings'],
            'properties' => [
                'things' => [
                    'type' => 'array',
                    'items' => [],
                ],
                'moreThings' => [
                    'type' => 'array',
                    'items' => [],
                ],
            ],
            'type' => 'object',
        ], json_decode($this->getModel('Bar')->toJson(), true));
    }

    public function testDictionaryModel(): void
    {
        $this->getOperation('/api/dictionary', 'get');
        self::assertEquals([
            'schema' => 'Dictionary',
            'required' => ['options', 'compoundOptions', 'nestedCompoundOptions', 'modelOptions', 'listOptions', 'arrayOrDictOptions', 'integerOptions'],
            'properties' => [
                'options' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        'type' => 'string',
                    ],
                ],
                'compoundOptions' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        'oneOf' => [
                            [
                                'type' => 'string',
                            ],
                            [
                                'type' => 'integer',
                            ],
                        ],
                    ],
                ],
                'nestedCompoundOptions' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => [
                            'oneOf' => [
                                [
                                    'type' => 'string',
                                ],
                                [
                                    'type' => 'integer',
                                ],
                            ],
                        ],
                    ],
                ],
                'modelOptions' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        '$ref' => '#/components/schemas/Foo',
                    ],
                ],
                'listOptions' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                    ],
                ],
                'arrayOrDictOptions' => [
                    'oneOf' => [
                        [
                            'type' => 'array',
                            'items' => [
                                'type' => 'string',
                            ],
                        ],
                        [
                            'type' => 'object',
                            'additionalProperties' => [
                                'type' => 'string',
                            ],
                        ],
                    ],
                ],
                'integerOptions' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        'type' => 'integer',
                    ],
                ],
            ],
            'type' => 'object',
        ], json_decode($this->getModel('Dictionary')->toJson(), true));
    }

    public function testEntityWithFalsyDefaults(): void
    {
        $model = $this->getModel('EntityWithFalsyDefaults');

        self::assertSame(Generator::UNDEFINED, $model->required);

        self::assertEquals([
            'schema' => 'EntityWithFalsyDefaults',
            'type' => 'object',
            'properties' => [
                'zero' => [
                    'type' => 'integer',
                    'default' => 0,
                ],
                'float' => [
                    'type' => 'number',
                    'format' => 'float',
                    'default' => 0.0,
                ],
                'empty' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'false' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
                'nullString' => [
                    'nullable' => true,
                    'type' => 'string',
                ],
                'array' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                    ],
                    'default' => [],
                ],
            ],
        ], json_decode($model->toJson(), true));
    }

    public function testRangeIntegers(): void
    {
        $expected = [
            'schema' => 'RangeInteger',
            'required' => ['rangeInt', 'minRangeInt', 'maxRangeInt'],
            'properties' => [
                'rangeInt' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 99,
                ],
                'minRangeInt' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
                'maxRangeInt' => [
                    'type' => 'integer',
                    'maximum' => 99,
                ],
                'nullableRangeInt' => [
                    'type' => 'integer',
                    'nullable' => true,
                    'minimum' => 1,
                    'maximum' => 99,
                ],
            ],
            'type' => 'object',
        ];

        if (version_compare(Kernel::VERSION, '6.1', '>=')) {
            array_unshift($expected['required'], 'positiveInt', 'negativeInt');
            $expected['properties'] += [
                'positiveInt' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
                'negativeInt' => [
                    'type' => 'integer',
                    'maximum' => -1,
                ],
            ];
        }

        self::assertEquals($expected, json_decode($this->getModel('RangeInteger')->toJson(), true));
    }
}
