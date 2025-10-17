<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Util;

use Nelmio\ApiDocBundle\Util\LegacyTypeConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\UnionType;

class LegacyTypeConverterTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(Type::class)) {
            self::markTestSkipped('Symfony TypeInfo component is not available.');
        }
    }

    #[DataProvider('provideToTypeInfoTypeCases')]
    public function testToTypeInfoType(?Type $expected, ?array $legacyTypes): void
    {
        self::assertEquals($expected, $converted = LegacyTypeConverter::toTypeInfoType($legacyTypes));

        // Ensure the conversion is reversible when possible
        if ($converted) {
            self::assertEquals($legacyTypes[0], LegacyTypeConverter::toLegacyType($converted));
        }
    }

    public static function provideToTypeInfoTypeCases(): \Generator
    {
        yield 'null' => [
            null,
            null,
        ];

        yield 'empty array' => [
            null,
            [],
        ];

        yield 'object' => [
            Type::object('Foo\Bar'),
            [new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, 'Foo\Bar')],
        ];

        yield 'nullable object' => [
            Type::nullable(Type::object('Foo\Bar')),
            [new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, true, 'Foo\Bar')],
        ];

        yield 'union' => [
            Type::union(Type::object('Foo\Bar'), Type::object('Foo\Baz')),
            [
                new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, 'Foo\Bar'),
                new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, 'Foo\Baz'),
            ],
        ];

        yield 'nullable union' => [
            Type::nullable(Type::union(Type::object('Foo\Bar'), Type::object('Foo\Baz'))),
            [
                new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, 'Foo\Bar'),
                new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, true, 'Foo\Baz'),
            ],
        ];

        yield 'array' => [
            Type::array(Type::object('Foo\Bar')),
            [new LegacyType(LegacyType::BUILTIN_TYPE_ARRAY, false, null, true, null, new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, 'Foo\Bar'))],
        ];

        yield 'collection' => [
            Type::collection(Type::object('Acme\Foo'), Type::object('Acme\Bar')),
            [new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, 'Acme\Foo', true, null, new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, 'Acme\Bar'))],
        ];
    }

    public function testToTypeInfoTypeWithUnsupportedTypeThrowsException(): void
    {
        $this->expectException(\LogicException::class);
        $legacyTypes = [new LegacyType(LegacyType::BUILTIN_TYPE_STRING)];
        LegacyTypeConverter::toTypeInfoType($legacyTypes);
    }

    #[DataProvider('provideToLegacyTypeCases')]
    public function testToLegacyType(LegacyType $expected, Type $type): void
    {
        self::assertEquals($expected, LegacyTypeConverter::toLegacyType($type));
    }

    public static function provideToLegacyTypeCases(): \Generator
    {
        yield 'object' => [
            new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, 'Foo\Bar'),
            Type::object('Foo\Bar'),
        ];

        yield 'nullable object' => [
            new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, true, 'Foo\Bar'),
            Type::nullable(Type::object('Foo\Bar')),
        ];
    }

    public function testToLegacyTypeWithUnsupportedTypeThrowsException(): void
    {
        $this->expectException(\LogicException::class);
        $type = Type::union(Type::object('Foo\Bar'), Type::object('Foo\Baz'));
        LegacyTypeConverter::toLegacyType($type);
    }

    #[DataProvider('provideCreateTypeCases')]
    public function testCreateType(Type $expected, string $typeString): void
    {
        self::assertEquals($expected, LegacyTypeConverter::createType($typeString));
    }

    public static function provideCreateTypeCases(): \Generator
    {
        yield 'simple' => [
            Type::object('Foo\Bar'),
            'Foo\Bar',
        ];

        yield 'array' => [
            Type::list(Type::object('Foo\Bar')),
            'Foo\Bar[]',
        ];

        yield 'nested array' => [
            Type::list(Type::list(Type::object('Foo\Bar'))),
            'Foo\Bar[][]',
        ];
    }
}