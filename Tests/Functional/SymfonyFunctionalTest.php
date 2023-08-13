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

use Symfony\Component\HttpKernel\Attribute\MapQueryString;

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
        $this->assertTrue($parameter->required);

        $parameter = $this->getParameter($operation, 'name', $in);
        $this->assertTrue($parameter->required);

        $parameter = $this->getParameter($operation, 'nullableName', $in);
        $this->assertFalse($parameter->required);

        $parameter = $this->getParameter($operation, 'articleType81', $in);

        $property = $this->getProperty($this->getModel('SymfonyMapQueryString'), 'articleType81');
        $this->assertTrue($parameter->required);
        $this->assertEquals($property, $parameter->schema);
    }

    public function testMapQueryStringParametersAreOptional(): void
    {
        if (!class_exists(MapQueryString::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryString attribute not found');
        }

        $operation = $this->getOperation('/api/article_map_query_string_nullable', 'get');

        $in = 'query';

        $parameter = $this->getParameter($operation, 'id', $in);
        $this->assertFalse($parameter->required);

        $parameter = $this->getParameter($operation, 'name', $in);
        $this->assertFalse($parameter->required);

        $parameter = $this->getParameter($operation, 'nullableName', $in);
        $this->assertFalse($parameter->required);

        $parameter = $this->getParameter($operation, 'articleType81', $in);
        $this->assertFalse($parameter->required);
    }

    public function testMapQueryStringParametersOverwriteParameters(): void
    {
        if (!class_exists(MapQueryString::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryString attribute not found');
        }

        $operation = $this->getOperation('/api/article_map_query_string_overwrite_parameters', 'get');

        foreach (['id', 'name', 'nullableName', 'articleType81'] as $name) {
            $parameter = $this->getParameter($operation, $name, 'query');
            $this->assertSame($parameter->description, sprintf('Query parameter %s description', $name));
        }
    }
}
