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

class LegacyTypeConverterTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(Type::class)) {
            self::markTestSkipped('Symfony TypeInfo component is not available.');
        }
    }

    /**
     * @param LegacyType[] $legacyTypes
     */
    #[DataProvider('provideToTypeInfoTypeCases')]
    public function testToTypeInfoType(?Type $expected, ?array $legacyTypes): void
    {
        self::assertEquals($expected, $converted = LegacyTypeConverter::toTypeInfoType($legacyTypes));

        // Ensure the conversion is reversible when possible
        if (null !== $converted) {
            self::assertEquals($legacyTypes[0], LegacyTypeConverter::toLegacyType($converted));
        }
    }

    public static function provideToTypeInfoTypeCases(): \Generator
    {
        if (!class_exists(Type::class)) {
            yield [null];

            return;
        }

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

        yield 'collection' => [
            Type::collection(Type::object(self::class), Type::object('Foo\Bar'), Type::int()),
            [
                new LegacyType(
                    LegacyType::BUILTIN_TYPE_OBJECT,
                    false,
                    self::class,
                    true,
                    collectionKeyType: new LegacyType(LegacyType::BUILTIN_TYPE_INT),
                    collectionValueType: new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, 'Foo\Bar'),
                ),
            ],
        ];

        yield 'array' => [
            Type::array(Type::object('Foo\Bar')),
            [
                new LegacyType(
                    LegacyType::BUILTIN_TYPE_ARRAY,
                    false,
                    null,
                    true,
                    [new LegacyType(LegacyType::BUILTIN_TYPE_INT), new LegacyType(LegacyType::BUILTIN_TYPE_STRING)],
                    new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, 'Foo\Bar'),
                ),
            ],
        ];

        yield 'array (string key)' => [
            Type::array(Type::object('Foo\Bar'), Type::string()),
            [
                new LegacyType(
                    LegacyType::BUILTIN_TYPE_ARRAY,
                    false,
                    null,
                    true,
                    new LegacyType(LegacyType::BUILTIN_TYPE_STRING),
                    new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, 'Foo\Bar'),
                ),
            ],
        ];

        yield 'array (int key)' => [
            Type::array(Type::object('Foo\Bar'), Type::int()),
            [
                new LegacyType(
                    LegacyType::BUILTIN_TYPE_ARRAY,
                    false,
                    null,
                    true,
                    new LegacyType(LegacyType::BUILTIN_TYPE_INT),
                    new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, 'Foo\Bar'),
                ),
            ],
        ];
    }

    public function testToTypeInfoTypeWithUnsupportedTypeThrowsException(): void
    {
        $this->expectException(\LogicException::class);
        $legacyTypes = [new LegacyType(LegacyType::BUILTIN_TYPE_BOOL)];
        LegacyTypeConverter::toTypeInfoType($legacyTypes);
    }

    #[DataProvider('provideToLegacyTypeCases')]
    public function testToLegacyType(LegacyType $expected, Type $type): void
    {
        self::assertEquals($expected, LegacyTypeConverter::toLegacyType($type));
    }

    public static function provideToLegacyTypeCases(): \Generator
    {
        if (!class_exists(Type::class)) {
            yield [null];

            return;
        }

        yield 'object' => [
            new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, 'Foo\Bar'),
            Type::object('Foo\Bar'),
        ];

        yield 'collection' => [
            new LegacyType(
                LegacyType::BUILTIN_TYPE_OBJECT,
                false,
                self::class,
                true,
                collectionKeyType: new LegacyType(LegacyType::BUILTIN_TYPE_INT),
                collectionValueType: new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, 'Foo\Bar'),
            ),
            Type::collection(Type::object(self::class), Type::object('Foo\Bar'), Type::int()),
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
        if (!class_exists(Type::class)) {
            yield 'object legacy type' => [
                new LegacyType('object', false, 'Foo\Bar'),
                'Foo\Bar',
            ];

            yield 'array legacy type' => [
                new LegacyType('array', false, null, true, null, new LegacyType('object', false, 'Foo\Bar')),
                'Foo\Bar[]',
            ];

            yield 'nested array legacy type' => [
                new LegacyType('array', false, null, true, null, new LegacyType('array', false, null, true, null, new LegacyType('object', false, 'Foo\Bar'))),
                'Foo\Bar[][]',
            ];

            return;
        }

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
