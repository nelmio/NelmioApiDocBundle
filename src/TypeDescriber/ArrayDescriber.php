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

        // When the key type is arrayKey() (int|string union), the collection has
        // no explicit key type (e.g. `array<T>` in PHPDoc). Treat as a JSON array
        // rather than splitting into anyOf: [array, object].
        $keyTypes = $type->getCollectionKeyType()->getTypes();
        if (2 === \count($keyTypes)
            && $keyTypes[0] instanceof Type\BuiltinType
            && $keyTypes[1] instanceof Type\BuiltinType
            && self::isArrayKeyUnion($keyTypes[0]->getTypeIdentifier(), $keyTypes[1]->getTypeIdentifier())
        ) {
            // When a Traversable object is used as a generic parameter
            // (e.g. `list<MyTraversableClass>`), StringTypeResolver wraps it in
            // CollectionType(ObjectType) with no key/value type info. Unwrap it
            // so ClassDescriber can create a proper $ref.
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

        $union = Type::union(
            ...$arrayTypes
        );

        $this->describer->describe($union, $schema, $context);
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof CollectionType
            && $type->getCollectionKeyType() instanceof Type\UnionType;
    }

    /**
     * Checks whether two type identifiers form an arrayKey() union (int|string),
     * regardless of which order TypeInfo emits them.
     */
    private static function isArrayKeyUnion(TypeIdentifier $a, TypeIdentifier $b): bool
    {
        return (TypeIdentifier::INT === $a && TypeIdentifier::STRING === $b)
            || (TypeIdentifier::STRING === $a && TypeIdentifier::INT === $b);
    }
}
