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

use OpenApi\Annotations as OA;

class FOSRestTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);
    }

    public function testFOSRestAction()
    {
        $operation = $this->getOperation('/api/fosrest', 'post');

        $this->assertHasParameter('foo', 'query', $operation);
        $this->assertInstanceOf(OA\RequestBody::class, $operation->requestBody);

        $bodySchema = $operation->requestBody->content['application\json']->schema;

        $this->assertHasProperty('bar', $bodySchema);
        $this->assertHasProperty('baz', $bodySchema);

        $fooParameter = $this->getParameter($operation, 'foo', 'query');
        $this->assertInstanceOf(OA\Schema::class, $fooParameter->schema);
        $this->assertEquals('\d+', $fooParameter->schema->pattern);
        $this->assertEquals(OA\UNDEFINED, $fooParameter->schema->format);

        $barProperty = $this->getProperty($bodySchema, 'bar');
        $this->assertEquals('\d+', $barProperty->pattern);
        $this->assertEquals(OA\UNDEFINED, $barProperty->format);

        $bazProperty = $this->getProperty($bodySchema, 'baz');
        $this->assertEquals(OA\UNDEFINED, $bazProperty->pattern);
        $this->assertEquals('IsTrue', $bazProperty->format);

        // The _format path attribute should be removed
        $this->assertNotHasParameter('_format', 'path', $operation);
    }
}
