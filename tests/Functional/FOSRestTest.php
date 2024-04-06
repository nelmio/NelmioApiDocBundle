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
use OpenApi\Generator;
use Symfony\Component\HttpKernel\KernelInterface;

class FOSRestTest extends WebTestCase
{
    /**
     * @param array<mixed> $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel(TestKernel::USE_FOSREST);
    }

    protected function setUp(): void
    {
        parent::setUp();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);
    }

    /**
     * @dataProvider provideRoute
     */
    public function testFOSRestAction(string $route): void
    {
        $operation = $this->getOperation($route, 'post');

        self::assertHasParameter('foo', 'query', $operation);
        self::assertInstanceOf(OA\RequestBody::class, $operation->requestBody);

        $bodySchema = $operation->requestBody->content['application/json']->schema;

        self::assertHasProperty('bar', $bodySchema);
        self::assertHasProperty('baz', $bodySchema);

        $fooParameter = $this->getParameter($operation, 'foo', 'query');
        self::assertInstanceOf(OA\Schema::class, $fooParameter->schema);
        self::assertEquals('\d+', $fooParameter->schema->pattern);
        self::assertEquals(Generator::UNDEFINED, $fooParameter->schema->format);

        $mappedParameter = $this->getParameter($operation, 'mapped[]', 'query');
        self::assertTrue($mappedParameter->explode);

        $barProperty = $this->getProperty($bodySchema, 'bar');
        self::assertEquals('\d+', $barProperty->pattern);
        self::assertEquals(Generator::UNDEFINED, $barProperty->format);

        $bazProperty = $this->getProperty($bodySchema, 'baz');
        self::assertEquals(Generator::UNDEFINED, $bazProperty->pattern);
        self::assertEquals('IsTrue', $bazProperty->format);

        $dateTimeProperty = $this->getProperty($bodySchema, 'datetime');
        self::assertEquals('date-time', $dateTimeProperty->format);

        $dateTimeAltProperty = $this->getProperty($bodySchema, 'datetimeAlt');
        self::assertEquals('date-time', $dateTimeAltProperty->format);

        $dateTimeNoFormatProperty = $this->getProperty($bodySchema, 'datetimeNoFormat');
        self::assertEquals(Generator::UNDEFINED, $dateTimeNoFormatProperty->format);

        $dateProperty = $this->getProperty($bodySchema, 'date');
        self::assertEquals('date', $dateProperty->format);

        // The _format path attribute should be removed
        self::assertNotHasParameter('_format', 'path', $operation);
    }

    public static function provideRoute(): \Generator
    {
        yield 'Annotations' => ['/api/fosrest'];

        if (TestKernel::isAttributesAvailable()) {
            yield 'Attributes' => ['/api/fosrest_attributes'];
        }
    }
}
