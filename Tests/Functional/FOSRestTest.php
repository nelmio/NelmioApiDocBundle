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
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

class FOSRestTest extends WebTestCase
{
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel(TestKernel::USE_FOSREST);
    }

    protected function setUp(): void
    {
        if (Kernel::MAJOR_VERSION >= 7) {
            $this->markTestSkipped('Not supported in symfony 7');
        }

        parent::setUp();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);
    }

    /**
     * @dataProvider provideRoute
     */
    public function testFOSRestAction(string $route)
    {
        $operation = $this->getOperation($route, 'post');

        $this->assertHasParameter('foo', 'query', $operation);
        $this->assertInstanceOf(OA\RequestBody::class, $operation->requestBody);

        $bodySchema = $operation->requestBody->content['application/json']->schema;

        $this->assertHasProperty('bar', $bodySchema);
        $this->assertHasProperty('baz', $bodySchema);

        $fooParameter = $this->getParameter($operation, 'foo', 'query');
        $this->assertInstanceOf(OA\Schema::class, $fooParameter->schema);
        $this->assertEquals('\d+', $fooParameter->schema->pattern);
        $this->assertEquals(Generator::UNDEFINED, $fooParameter->schema->format);

        $mappedParameter = $this->getParameter($operation, 'mapped[]', 'query');
        $this->assertTrue($mappedParameter->explode);

        $barProperty = $this->getProperty($bodySchema, 'bar');
        $this->assertEquals('\d+', $barProperty->pattern);
        $this->assertEquals(Generator::UNDEFINED, $barProperty->format);

        $bazProperty = $this->getProperty($bodySchema, 'baz');
        $this->assertEquals(Generator::UNDEFINED, $bazProperty->pattern);
        $this->assertEquals('IsTrue', $bazProperty->format);

        $dateTimeProperty = $this->getProperty($bodySchema, 'datetime');
        $this->assertEquals('date-time', $dateTimeProperty->format);

        $dateTimeAltProperty = $this->getProperty($bodySchema, 'datetimeAlt');
        $this->assertEquals('date-time', $dateTimeAltProperty->format);

        $dateTimeNoFormatProperty = $this->getProperty($bodySchema, 'datetimeNoFormat');
        $this->assertEquals(Generator::UNDEFINED, $dateTimeNoFormatProperty->format);

        $dateProperty = $this->getProperty($bodySchema, 'date');
        $this->assertEquals('date', $dateProperty->format);

        // The _format path attribute should be removed
        $this->assertNotHasParameter('_format', 'path', $operation);
    }

    public function provideRoute(): iterable
    {
        yield 'Annotations' => ['/api/fosrest'];

        if (TestKernel::isAttributesAvailable()) {
            yield 'Attributes' => ['/api/fosrest_attributes'];
        }
    }
}
