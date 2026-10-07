<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\TypeDescriber;

use OpenApi\Annotations\Schema;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * @implements TypeDescriberInterface<CollectionType>
 *
 * @internal
 */
final class ArrayDescriber implements TypeDescriberInterface, TypeDescriberAwareInterface
{
    use TypeDescriberAwareTrait;

    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        if (!$type->getCollectionKeyType() instanceof Type\UnionType) {
            throw new \LogicException('This describer only supports '.CollectionType::class.' with '.Type\UnionType::class.' as key type.');
        }

        // TypeInfo cannot tell `array<T>`, `array<array-key, T>` and `array<int|string, T>`
        // apart: they all get an int|string key union. Describe them as a JSON array rather
        // than splitting them into anyOf: [array, object]. Array shapes are left out, as one
        // with mixed keys (e.g. `array{0: int, foo: string}`) is serialized as a JSON object.
        // ArrayShapeType only exists since symfony/type-info 7.3, hence the class name string.
        if (!is_a($type, 'Symfony\Component\TypeInfo\Type\ArrayShapeType')
            && self::isIntOrStringUnion($type->getCollectionKeyType())
        ) {
            // StringTypeResolver wraps a non-generic Traversable or ArrayAccess class
            // reference (e.g. `\ArrayObject`, or the `T` in `list<T>`) in a
            // CollectionType(ObjectType) with no key/value type info. Unwrap it so
            // ClassDescriber can create a proper $ref.
            $wrappedType = $type->getWrappedType();
            if ($wrappedType instanceof Type\ObjectType) {
                $this->describer->describe($wrappedType, $schema, $context);

                return;
            }

            $this->describer->describe(Type::list($type->getCollectionValueType()), $schema, $context);

            return;
        }

        $arrayTypes = array_map(
            static fn (Type $keyType): Type => Type::array($type->getCollectionValueType(), $keyType),
            $type->getCollectionKeyType()->getTypes()
        );

        // A single-member key union (e.g. `array<'foo'|'bar', T>` once literals are
        // deduplicated) must not go through Type::union(), which turns it into a nullable.
        $this->describer->describe(
            1 === \count($arrayTypes) ? $arrayTypes[0] : Type::union(...$arrayTypes),
            $schema,
            $context
        );
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof CollectionType
            && $type->getCollectionKeyType() instanceof Type\UnionType;
    }

    /**
     * TypeInfo sorts union members by their string representation, so `int` always comes first.
     */
    private static function isIntOrStringUnion(Type\UnionType $keyType): bool
    {
        $identifiers = array_map(
            static fn (Type $type): ?TypeIdentifier => $type instanceof Type\BuiltinType ? $type->getTypeIdentifier() : null,
            $keyType->getTypes()
        );

        return [TypeIdentifier::INT, TypeIdentifier::STRING] === $identifiers;
    }
}
