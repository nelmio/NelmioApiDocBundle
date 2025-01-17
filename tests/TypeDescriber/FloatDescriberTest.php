<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\TypeDescriber;

use Nelmio\ApiDocBundle\TypeDescriber\FloatDescriber;
use OpenApi\Annotations as OA;
use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolver;

class FloatDescriberTest extends TestCase
{
    private FloatDescriber $typeDescriber;

    protected function setUp(): void
    {
        if (!class_exists(Type::class)) {
            self::markTestSkipped('The "symfony/type-info" package is not available.');
        }

        $this->typeDescriber = new FloatDescriber();
    }

    /**
     * @dataProvider provideTypes
     *
     * @param array<string, mixed> $context
     */
    public function testDescribe(Type $type, array $context = []): void
    {
        $schema = new OA\Schema([]);

        self::assertTrue($this->typeDescriber->supports($type, $context));

        $this->typeDescriber->describe($type, $schema, $context);

        self::assertSame([
            'type' => 'number',
            'format' => 'float',
        ], json_decode($schema->toJson(), true));
    }

    public static function provideTypes(): \Generator
    {
        $typeResolver = TypeResolver::create();

        yield [
            Type::float(),
        ];

        yield [
            $typeResolver->resolve(new \ReflectionProperty(new class {
                public float $property;
            }, 'property')),
        ];

        yield [
            $typeResolver->resolve(new \ReflectionMethod(new class {
                public function method(): float
                {
                }
            }, 'method')),
        ];
    }
}
