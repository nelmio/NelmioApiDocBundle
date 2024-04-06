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

use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class SymfonyFunctionalTest extends WebTestCase
{
    protected function setUp(): void
    {
        if (PHP_VERSION_ID < 80100) {
            self::markTestSkipped('Attributes require PHP 8');
        }

        parent::setUp();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);
    }

    public function testMapQueryStringModelGetsCreated(): void
    {
        if (!class_exists(MapQueryString::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryString attribute not found');
        }

        $expected = [
            'schema' => 'SymfonyMapQueryString',
            'required' => [
                'id',
                'name',
                'articleType81',
            ],
            'properties' => [
                'id' => [
                    'type' => 'integer',
                ],
                'name' => [
                    'type' => 'string',
                ],
                'nullableName' => [
                    'type' => 'string',
                    'nullable' => true,
                ],
                'articleType81' => [
                    '$ref' => '#/components/schemas/ArticleType81',
                ],
                'nullableArticleType81' => [
                    'nullable' => true,
                    'oneOf' => [
                        ['$ref' => '#/components/schemas/ArticleType81'],
                    ],
                ],
            ],
            'type' => 'object',
        ];

        self::assertSame($expected, json_decode($this->getModel('SymfonyMapQueryString')->toJson(), true));
    }

    public function testMapQueryString(): void
    {
        if (!class_exists(MapQueryString::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryString attribute not found');
        }

        self::assertEquals([
            'operationId' => 'get_api_nelmio_apidoc_tests_functional_api_fetcharticlefrommapquerystring',
            'parameters' => [
                [
                    'name' => 'id',
                    'in' => 'query',
                    'required' => true,
                    'schema' => [
                        'type' => 'integer',
                    ],
                ],
                [
                    'name' => 'name',
                    'in' => 'query',
                    'required' => true,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                [
                    'name' => 'nullableName',
                    'in' => 'query',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                        'nullable' => true,
                    ],
                ],
                [
                    'name' => 'articleType81',
                    'in' => 'query',
                    'required' => true,
                    'schema' => [
                        '$ref' => '#/components/schemas/ArticleType81',
                    ],
                ],
                [
                    'name' => 'nullableArticleType81',
                    'in' => 'query',
                    'required' => false,
                    'schema' => [
                        'nullable' => true,
                        'oneOf' => [
                            ['$ref' => '#/components/schemas/ArticleType81'],
                        ],
                    ],
                ],
            ],
            'responses' => [
                '200' => [
                    'description' => '',
                ],
            ],
        ], json_decode($this->getOperation('/api/article_map_query_string', 'get')->toJson(), true));
    }

    public function testMapQueryStringParametersAreOptional(): void
    {
        if (!class_exists(MapQueryString::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryString attribute not found');
        }

        self::assertEquals([
            'operationId' => 'get_api_nelmio_apidoc_tests_functional_api_fetcharticlefrommapquerystringnullable',
            'parameters' => [
                [
                    'name' => 'id',
                    'in' => 'query',
                    'required' => false,
                    'schema' => [
                        'type' => 'integer',
                    ],
                ],
                [
                    'name' => 'name',
                    'in' => 'query',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                [
                    'name' => 'nullableName',
                    'in' => 'query',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                        'nullable' => true,
                    ],
                ],
                [
                    'name' => 'articleType81',
                    'in' => 'query',
                    'required' => false,
                    'schema' => [
                        '$ref' => '#/components/schemas/ArticleType81',
                    ],
                ],
                [
                    'name' => 'nullableArticleType81',
                    'in' => 'query',
                    'required' => false,
                    'schema' => [
                        'nullable' => true,
                        'oneOf' => [
                            ['$ref' => '#/components/schemas/ArticleType81'],
                        ],
                    ],
                ],
            ],
            'responses' => [
                '200' => [
                    'description' => '',
                ],
            ],
        ], json_decode($this->getOperation('/api/article_map_query_string_nullable', 'get')->toJson(), true));
    }

    public function testMapQueryStringParametersOverwriteParameters(): void
    {
        if (!class_exists(MapQueryString::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryString attribute not found');
        }

        self::assertEquals([
            'operationId' => 'get_api_nelmio_apidoc_tests_functional_api_fetcharticlefrommapquerystringoverwriteparameters',
            'parameters' => [
                [
                    'name' => 'id',
                    'in' => 'query',
                    'required' => true,
                    'schema' => [
                        'type' => 'string',
                        'nullable' => true,
                    ],
                    'description' => 'Query parameter id description',
                ],
                [
                    'name' => 'name',
                    'in' => 'query',
                    'required' => true,
                    'schema' => [
                        'type' => 'string',
                    ],
                    'description' => 'Query parameter name description',
                ],
                [
                    'name' => 'nullableName',
                    'in' => 'query',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                        'nullable' => true,
                    ],
                    'description' => 'Query parameter nullableName description',
                ],
                [
                    'name' => 'articleType81',
                    'in' => 'query',
                    'required' => true,
                    'schema' => [
                        '$ref' => '#/components/schemas/ArticleType81',
                    ],
                    'description' => 'Query parameter articleType81 description',
                ],
                [
                    'name' => 'nullableArticleType81',
                    'in' => 'query',
                    'required' => false,
                    'schema' => [
                        'nullable' => true,
                        'oneOf' => [
                            ['$ref' => '#/components/schemas/ArticleType81'],
                        ],
                    ],
                    'description' => 'Query parameter nullableArticleType81 description',
                ],
            ],
            'responses' => [
                '200' => [
                    'description' => '',
                ],
            ],
        ], json_decode($this->getOperation('/api/article_map_query_string_overwrite_parameters', 'get')->toJson(), true));
    }

    public function testMapQueryParameter(): void
    {
        if (!class_exists(MapQueryParameter::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryParameter attribute not found');
        }

        self::assertEquals([
            [
                'name' => 'someInt',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'type' => 'integer',
                ],
            ],
            [
                'name' => 'someFloat',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'type' => 'number',
                    'format' => 'float',
                ],
            ],
            [
                'name' => 'someBool',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'type' => 'boolean',
                ],
            ],
            [
                'name' => 'someString',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                ],
            ],
            [
                'name' => 'someArray[]',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'type' => 'array',
                    'items' => [],
                ],
            ],
        ], json_decode($this->getOperation('/api/article_map_query_parameter', 'get')->toJson(), true)['parameters']);
    }

    public function testMapQueryParameterHandlesNullable(): void
    {
        if (!class_exists(MapQueryParameter::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryParameter attribute not found');
        }

        self::assertEquals([
            'operationId' => 'get_api_nelmio_apidoc_tests_functional_api_fetcharticlefrommapqueryparameternullable',
            'parameters' => [
                [
                    'name' => 'id',
                    'in' => 'query',
                    'required' => false,
                    'schema' => [
                        'type' => 'integer',
                        'nullable' => true,
                    ],
                ],
            ],
            'responses' => [
                '200' => [
                    'description' => '',
                ],
            ],
        ], json_decode($this->getOperation('/api/article_map_query_parameter_nullable', 'get')->toJson(), true));
    }

    public function testMapQueryParameterHandlesDefault(): void
    {
        if (!class_exists(MapQueryParameter::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryParameter attribute not found');
        }

        self::assertEquals([
            'operationId' => 'get_api_nelmio_apidoc_tests_functional_api_fetcharticlefrommapqueryparameterdefault',
            'parameters' => [
                [
                    'name' => 'id',
                    'in' => 'query',
                    'required' => false,
                    'schema' => [
                        'type' => 'integer',
                        'default' => 123,
                    ],
                ],
            ],
            'responses' => [
                '200' => [
                    'description' => '',
                ],
            ],
        ], json_decode($this->getOperation('/api/article_map_query_parameter_default', 'get')->toJson(), true));
    }

    public function testMapQueryParameterOverwriteParameter(): void
    {
        if (!class_exists(MapQueryParameter::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryParameter attribute not found');
        }

        self::assertEquals([
            'operationId' => 'get_api_nelmio_apidoc_tests_functional_api_fetcharticlefrommapqueryparameteroverwriteparameters',
            'parameters' => [
                [
                    'name' => 'id',
                    'in' => 'query',
                    'required' => false,
                    'schema' => [
                        'type' => 'integer',
                        'nullable' => true,
                    ],
                    'description' => 'Query parameter id description',
                    'example' => 123,
                ],
                [
                    'name' => 'changedType',
                    'in' => 'query',
                    'required' => false,
                    'schema' => [
                        'type' => 'int',
                        'nullable' => false,
                    ],
                    'description' => 'Incorrectly described query parameter',
                    'example' => 123,
                ],
            ],
            'responses' => [
                '200' => [
                    'description' => '',
                ],
            ],
        ], json_decode($this->getOperation('/api/article_map_query_parameter_overwrite_parameters', 'get')->toJson(), true));
    }

    public function testMapRequestPayload(): void
    {
        if (!class_exists(MapRequestPayload::class)) {
            self::markTestSkipped('Symfony 6.3 MapRequestPayload attribute not found');
        }

        self::assertEquals([
            'operationId' => 'post_api_nelmio_apidoc_tests_functional_api_createarticlefrommaprequestpayload',
            'responses' => [
                '200' => [
                    'description' => '',
                ],
            ],
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Article81',
                        ],
                    ],
                ],
                'required' => true,
            ],
        ], json_decode($this->getOperation('/api/article_map_request_payload', 'post')->toJson(), true));
    }

    public function testMapRequestPayloadNullable(): void
    {
        if (!class_exists(MapRequestPayload::class)) {
            self::markTestSkipped('Symfony 6.3 MapRequestPayload attribute not found');
        }

        self::assertEquals([
            'operationId' => 'post_api_nelmio_apidoc_tests_functional_api_createarticlefrommaprequestpayloadnullable',
            'responses' => [
                '200' => [
                    'description' => '',
                ],
            ],
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'nullable' => true,
                            'oneOf' => [
                                ['$ref' => '#/components/schemas/Article81'],
                            ],
                        ],
                    ],
                ],
                'required' => false,
            ],
        ], json_decode($this->getOperation('/api/article_map_request_payload_nullable', 'post')->toJson(), true));
    }

    public function testMapRequestPayloadOverwriteRequestBody(): void
    {
        if (!class_exists(MapRequestPayload::class)) {
            self::markTestSkipped('Symfony 6.3 MapRequestPayload attribute not found');
        }

        self::assertEquals([
            'operationId' => 'post_api_nelmio_apidoc_tests_functional_api_createarticlefrommaprequestpayloadoverwrite',
            'responses' => [
                '200' => [
                    'description' => '',
                ],
            ],
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/EntityWithNullableSchemaSet',
                        ],
                    ],
                ],
                'required' => true,
                'description' => 'Request body description',
            ],
        ], json_decode($this->getOperation('/api/article_map_request_payload_overwrite', 'post')->toJson(), true));
    }

    public function testMapRequestPayloadHandlesAlreadySetContent(): void
    {
        if (!class_exists(MapRequestPayload::class)) {
            self::markTestSkipped('Symfony 6.3 MapRequestPayload attribute not found');
        }

        self::assertEquals([
            'operationId' => 'post_api_nelmio_apidoc_tests_functional_api_createarticlefrommaprequestpayloadhandlesalreadysetcontent',
            'responses' => [
                '200' => [
                    'description' => '',
                ],
            ],
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Article81',
                        ],
                    ],
                ],
                'required' => true,
                'description' => 'Request body description',
            ],
        ], json_decode($this->getOperation('/api/article_map_request_payload_handles_already_set_content', 'post')->toJson(), true));
    }

    public function testMapRequestPayloadPassesValidationGroups(): void
    {
        if (!class_exists(MapRequestPayload::class)) {
            self::markTestSkipped('Symfony 6.3 MapRequestPayload attribute not found');
        }

        self::assertEquals([
            'operationId' => 'post_api_nelmio_apidoc_tests_functional_api_createarticlefrommaprequestpayloadpassedvalidationgroups',
            'responses' => [
                '200' => [
                    'description' => '',
                ],
            ],
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/SymfonyConstraintsTestGroup',
                        ],
                    ],
                ],
                'required' => true,
            ],
        ], json_decode($this->getOperation('/api/article_map_request_payload_validation_groups', 'post')->toJson(), true));
    }

    public function testMapQueryStringPassesValidationGroups(): void
    {
        if (!class_exists(MapQueryString::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryString attribute not found');
        }

        self::assertEquals([
            'operationId' => 'post_api_nelmio_apidoc_tests_functional_api_fetcharticlefrommapquerystringhandlesvalidationgroups',
            'responses' => [
                '200' => [
                    'description' => '',
                ],
            ],
            'parameters' => [
                [
                    'name' => 'property',
                    'in' => 'query',
                    'required' => true,
                    'schema' => [
                        'type' => 'integer',
                        'maximum' => 100,
                        'minimum' => 1,
                    ],
                ],
            ],
        ], json_decode($this->getOperation('/api/article_map_query_string_passes_validation_groups', 'post')->toJson(), true));
    }

    public function testMapQueryStringHandlesManyParameters(): void
    {
        if (!class_exists(MapQueryString::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryString attribute not found');
        }

        self::assertEquals([
            [
                'name' => 'filter',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'oneOf' => [
                        ['type' => 'string'],
                        ['type' => 'integer'],
                    ],
                ],
            ],
            [
                'name' => 'filterBy',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                ],
            ],
            [
                'name' => 'offset',
                'in' => 'query',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'default' => 0,
                ],
            ],
            [
                'name' => 'limit',
                'in' => 'query',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'default' => 10,
                ],
            ],
            [
                'name' => 'sortBy',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                ],
            ],
            [
                'name' => 'orderBy',
                'in' => 'query',
                'required' => false,
                'schema' => [
                    '$ref' => '#/components/schemas/SortEnum',
                ],
            ],
            [
                'name' => 'ids[]',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'type' => 'array',
                    'items' => [],
                ],
            ],
            [
                'name' => 'productIds[]',
                'in' => 'query',
                'required' => false,
                'description' => 'List of product ids',
                'schema' => [
                    'type' => 'array',
                    'description' => 'List of product ids',
                    'items' => [
                        'type' => 'integer',
                    ],
                ],
            ],
        ], json_decode($this->getOperation('/api/article_map_query_string_many_parameters', 'get')->toJson(), true)['parameters']);
    }

    public function testMapQueryStringHandlesManyParametersOptional(): void
    {
        if (!class_exists(MapQueryString::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryString attribute not found');
        }

        self::assertEquals([
            [
                'name' => 'filter',
                'in' => 'query',
                'required' => false,
                'schema' => [
                    'oneOf' => [
                        ['type' => 'string'],
                        ['type' => 'integer'],
                    ],
                ],
            ],
            [
                'name' => 'filterBy',
                'in' => 'query',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                ],
            ],
            [
                'name' => 'offset',
                'in' => 'query',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'default' => 0,
                ],
            ],
            [
                'name' => 'limit',
                'in' => 'query',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'default' => 10,
                ],
            ],
            [
                'name' => 'sortBy',
                'in' => 'query',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                ],
            ],
            [
                'name' => 'orderBy',
                'in' => 'query',
                'required' => false,
                'schema' => [
                    '$ref' => '#/components/schemas/SortEnum',
                ],
            ],
            [
                'name' => 'ids[]',
                'in' => 'query',
                'required' => false,
                'schema' => [
                    'type' => 'array',
                    'items' => [],
                ],
            ],
            [
                'name' => 'productIds[]',
                'in' => 'query',
                'required' => false,
                'description' => 'List of product ids',
                'schema' => [
                    'type' => 'array',
                    'description' => 'List of product ids',
                    'items' => [
                        'type' => 'integer',
                    ],
                ],
            ],
        ], json_decode($this->getOperation('/api/article_map_query_string_many_parameters_optional', 'get')->toJson(), true)['parameters']);
    }

    public function testMapQueryParameterHandlesValidateFilters(): void
    {
        if (!class_exists(MapQueryParameter::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryParameter attribute not found');
        }

        self::assertEquals([
            [
                'name' => 'minMaxInt',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'type' => 'integer',
                    'minimum' => 2,
                    'maximum' => 1234,
                ],
            ],
            [
                'name' => 'domain',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                    'format' => 'hostname',
                ],
            ],
            [
                'name' => 'email',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                    'format' => 'email',
                ],
            ],
            [
                'name' => 'ip',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                    'format' => 'ip',
                ],
            ],
            [
                'name' => 'ipv4',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                    'format' => 'ipv4',
                ],
            ],
            [
                'name' => 'ipv6',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                    'format' => 'ipv6',
                ],
            ],
            [
                'name' => 'macAddress',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                    'format' => 'mac',
                ],
            ],
            [
                'name' => 'regexp',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                    'pattern' => '/^test/',
                ],
            ],
            [
                'name' => 'url',
                'in' => 'query',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                    'format' => 'uri',
                ],
            ],
        ], json_decode($this->getOperation('/api/article_map_query_parameter_validate_filters', 'get')->toJson(), true)['parameters']);
    }
}
