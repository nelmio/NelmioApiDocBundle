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

use Nelmio\ApiDocBundle\TypeDescriber\ArrayDescriber;
use Nelmio\ApiDocBundle\TypeDescriber\TypeDescriberInterface;
use OpenApi\Annotations\Schema;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Exception\InvalidArgumentException;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\ArrayShapeType;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\TypeInfo\Type\TemplateType;
use Symfony\Component\TypeInfo\Type\UnionType;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\TypeInfo\TypeResolver\StringTypeResolver;

class ArrayDescriberTest extends TestCase
{
    private ArrayDescriber $describer;

    protected function setUp(): void
    {
        $this->describer = new ArrayDescriber();
    }

    /**
     * @dataProvider provideInvalidCollectionTypes
     *
     * @param CollectionType $type
     */
    public function testDescribeHandlesInvalidKeyType($type): void
    {
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('This describer only supports '.CollectionType::class.' with '.UnionType::class.' as key type.');

        $this->describer->describe($type, new Schema([]));
    }

    public static function provideInvalidCollectionTypes(): \Generator
    {
        yield [Type::array(Type::int(), Type::int())];
        yield [Type::array(Type::int(), Type::string())];
        yield [Type::list()];
        yield [Type::dict()];
    }

    /**
     * When the key type is the int|string union, the describer should treat the
     * collection as a list rather than splitting into anyOf [array, object].
     *
     * @dataProvider provideArrayKeyUnionCollectionTypes
     */
    public function testArrayKeyUnionIsTreatedAsList(CollectionType $type, TypeIdentifier $expectedValueType): void
    {
        $this->assertDescribesWith($type, self::callback(static function (Type $type) use ($expectedValueType): bool {
            return $type instanceof CollectionType
                && $type->isList()
                && self::isBuiltin($type->getCollectionValueType(), $expectedValueType);
        }));
    }

    public static function provideArrayKeyUnionCollectionTypes(): \Generator
    {
        $resolver = new StringTypeResolver();

        // TypeInfo resolves these with an int|string key union, as it does `array<int|string, T>` and `array<array-key, T>`
        yield 'array<T>' => [$resolver->resolve('array<string>'), TypeIdentifier::STRING];
        yield 'array' => [$resolver->resolve('array'), TypeIdentifier::MIXED];
        yield 'iterable<T>' => [$resolver->resolve('iterable<string>'), TypeIdentifier::STRING];
        // A generic Traversable class is described by its value type, not unwrapped
        yield 'Traversable<T>' => [$resolver->resolve('\ArrayObject<string>'), TypeIdentifier::STRING];
    }

    /**
     * A single-member key union (e.g. `array<'foo'|'bar', T>` once TypeInfo deduplicates
     * the literals) should be described as that array type, without becoming nullable.
     */
    public function testSingleMemberKeyUnionIsNotMadeNullable(): void
    {
        $type = (new StringTypeResolver())->resolve("array<'foo'|'bar', string>");
        self::assertInstanceOf(CollectionType::class, $type);

        $this->assertDescribesWith($type, self::callback(static function (Type $type): bool {
            return $type instanceof CollectionType
                && !$type->isNullable()
                && self::isBuiltin($type->getCollectionKeyType(), TypeIdentifier::STRING)
                && self::isBuiltin($type->getCollectionValueType(), TypeIdentifier::STRING);
        }));
    }

    /**
     * Any other key union (e.g. `array<K|int, T>` with a template type) is still split
     * into a union of arrays, one per key type.
     */
    public function testOtherKeyUnionIsSplitIntoUnionOfArrays(): void
    {
        try {
            $type = Type::array(Type::string(), Type::union(Type::template('K', Type::string()), Type::int()));
        } catch (InvalidArgumentException) {
            self::markTestSkipped('This symfony/type-info version does not accept template types as array keys.');
        }

        $this->assertDescribesWith($type, self::callback(static function (Type $type): bool {
            if (!$type instanceof UnionType || $type->isNullable()) {
                return false;
            }

            $keyTypes = [];
            foreach ($type->getTypes() as $arrayType) {
                if (!$arrayType instanceof CollectionType || !self::isBuiltin($arrayType->getCollectionValueType(), TypeIdentifier::STRING)) {
                    return false;
                }

                $keyType = $arrayType->getCollectionKeyType();
                $keyTypes[] = $keyType instanceof TemplateType ? 'template' : (self::isBuiltin($keyType, TypeIdentifier::INT) ? 'int' : 'other');
            }
            sort($keyTypes);

            return ['int', 'template'] === $keyTypes;
        }));
    }

    /**
     * An array shape with mixed keys (e.g. `array{0: int, foo: string}`) is serialized as
     * a JSON object, so it must not be described as a list.
     */
    public function testArrayShapeWithMixedKeysIsNotTreatedAsList(): void
    {
        if (!class_exists(ArrayShapeType::class)) {
            self::markTestSkipped('Array shapes require symfony/type-info 7.3 or later.');
        }

        $type = (new StringTypeResolver())->resolve('array{0: int, foo: string}');
        self::assertInstanceOf(CollectionType::class, $type);

        $this->assertDescribesWith($type, self::callback(static function (Type $type): bool {
            if (!$type instanceof UnionType) {
                return false;
            }

            foreach ($type->getTypes() as $arrayType) {
                if (!$arrayType instanceof CollectionType || $arrayType->isList()) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * StringTypeResolver wraps a non-generic Traversable class reference in
     * CollectionType(ObjectType): the describer should delegate the ObjectType directly.
     */
    public function testTraversableObjectIsUnwrapped(): void
    {
        $type = (new StringTypeResolver())->resolve('\ArrayObject');
        self::assertInstanceOf(CollectionType::class, $type);
        self::assertInstanceOf(ObjectType::class, $type->getWrappedType());

        $this->assertDescribesWith($type, self::identicalTo($type->getWrappedType()));
    }

    /**
     * Asserts that describing $type delegates exactly once to the inner describer, with a
     * type matching $expectedType and the same schema and context.
     */
    private function assertDescribesWith(CollectionType $type, Constraint $expectedType): void
    {
        $schema = new Schema([]);
        $context = ['foo' => 'bar'];

        $innerDescriber = $this->createMock(TypeDescriberInterface::class);
        $innerDescriber->expects(self::once())
            ->method('describe')
            ->with($expectedType, self::identicalTo($schema), $context);

        $this->describer->setDescriber($innerDescriber);
        $this->describer->describe($type, $schema, $context);
    }

    private static function isBuiltin(Type $type, TypeIdentifier $identifier): bool
    {
        return $type instanceof BuiltinType && $identifier === $type->getTypeIdentifier();
    }
}
