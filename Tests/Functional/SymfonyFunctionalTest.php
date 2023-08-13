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

use OpenApi\Annotations\Components;
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
            ],
            'type' => 'object',
        ];

        $this->assertSame($expected, json_decode($this->getModel('SymfonyMapQueryString')->toJson(), true));
    }

    public function testMapQueryString(): void
    {
        if (!class_exists(MapQueryString::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryString attribute not found');
        }

        $operation = $this->getOperation('/api/article_map_query_string', 'get');

        $in = 'query';

        $parameter = $this->getParameter($operation, 'id', $in);
        self::assertTrue($parameter->required);

        $parameter = $this->getParameter($operation, 'name', $in);
        self::assertTrue($parameter->required);

        $parameter = $this->getParameter($operation, 'nullableName', $in);
        self::assertFalse($parameter->required);

        $parameter = $this->getParameter($operation, 'articleType81', $in);

        $property = $this->getProperty($this->getModel('SymfonyMapQueryString'), 'articleType81');
        self::assertTrue($parameter->required);
        self::assertEquals($property, $parameter->schema);
    }

    public function testMapQueryStringParametersAreOptional(): void
    {
        if (!class_exists(MapQueryString::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryString attribute not found');
        }

        $operation = $this->getOperation('/api/article_map_query_string_nullable', 'get');

        $in = 'query';

        $parameter = $this->getParameter($operation, 'id', $in);
        self::assertFalse($parameter->required);

        $parameter = $this->getParameter($operation, 'name', $in);
        self::assertFalse($parameter->required);

        $parameter = $this->getParameter($operation, 'nullableName', $in);
        self::assertFalse($parameter->required);

        $parameter = $this->getParameter($operation, 'articleType81', $in);
        self::assertFalse($parameter->required);
    }

    public function testMapQueryStringParametersOverwriteParameters(): void
    {
        if (!class_exists(MapQueryString::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryString attribute not found');
        }

        $operation = $this->getOperation('/api/article_map_query_string_overwrite_parameters', 'get');

        foreach (['id', 'name', 'nullableName', 'articleType81'] as $name) {
            $parameter = $this->getParameter($operation, $name, 'query');
            self::assertSame($parameter->description, sprintf('Query parameter %s description', $name));
        }
    }

    public function testMapQueryParameter(): void
    {
        if (!class_exists(MapQueryParameter::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryParameter attribute not found');
        }

        $operation = $this->getOperation('/api/article_map_query_parameter', 'get');
        $in = 'query';

        $parameter = $this->getParameter($operation, 'id', $in);
        self::assertTrue($parameter->required);
        self::assertSame('integer', $parameter->schema->type);
    }

    public function testMapQueryParameterHandlesNullable(): void
    {
        if (!class_exists(MapQueryParameter::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryParameter attribute not found');
        }

        $operation = $this->getOperation('/api/article_map_query_parameter_nullable', 'get');
        $in = 'query';

        $parameter = $this->getParameter($operation, 'id', $in);
        self::assertFalse($parameter->required);
    }

    public function testMapQueryParameterHandlesDefault(): void
    {
        if (!class_exists(MapQueryParameter::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryParameter attribute not found');
        }

        $operation = $this->getOperation('/api/article_map_query_parameter_default', 'get');
        $in = 'query';

        $parameter = $this->getParameter($operation, 'id', $in);
        self::assertFalse($parameter->required);
        self::assertSame(123, $parameter->schema->default);
    }

    public function testMapQueryParameterOverwriteParameter(): void
    {
        if (!class_exists(MapQueryParameter::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryParameter attribute not found');
        }

        $operation = $this->getOperation('/api/article_map_query_parameter_overwrite_parameters', 'get');
        $in = 'query';

        $parameter = $this->getParameter($operation, 'id', $in);
        self::assertSame(123, $parameter->example);
        self::assertSame('Query parameter id description', $parameter->description);
    }

    public function testMapRequestPayload(): void
    {
        if (!class_exists(MapRequestPayload::class)) {
            self::markTestSkipped('Symfony 6.3 MapRequestPayload attribute not found');
        }

        $operation = $this->getOperation('/api/article_map_request_payload', 'post');

        $requestBody = $operation->requestBody;
        self::assertTrue($requestBody->required);

        self::assertCount(1, $requestBody->content);
        self::assertArrayHasKey('application/json', $requestBody->content);

        $media = $requestBody->content['application/json'];

        self::assertSame('application/json', $media->mediaType);

        $model = $this->getModel('Article81');
        self::assertSame(Components::SCHEMA_REF.$model->schema, $media->schema->ref);
    }

    public function testMapRequestPayloadNullable(): void
    {
        if (!class_exists(MapRequestPayload::class)) {
            self::markTestSkipped('Symfony 6.3 MapRequestPayload attribute not found');
        }

        $operation = $this->getOperation('/api/article_map_request_payload_nullable', 'post');

        $requestBody = $operation->requestBody;
        self::assertFalse($requestBody->required);
    }

    public function testMapRequestPayloadOverwriteRequestBody(): void
    {
        if (!class_exists(MapRequestPayload::class)) {
            self::markTestSkipped('Symfony 6.3 MapRequestPayload attribute not found');
        }

        $operation = $this->getOperation('/api/article_map_request_payload_overwrite', 'post');

        $requestBody = $operation->requestBody;
        self::assertSame('Request body description', $requestBody->description);
    }
}
