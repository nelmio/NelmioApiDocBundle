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
use Symfony\Component\TypeInfo\Type\ArrayShapeType;
use Symfony\Component\TypeInfo\Type\CollectionType;

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
        $keyType = $type->getCollectionKeyType();
        if (!$keyType instanceof Type\UnionType) {
            throw new \LogicException('This describer only supports '.CollectionType::class.' with '.Type\UnionType::class.' as key type.');
        }

        // `array<T>` gets the same int|string key as `array<array-key, T>`, so the key is unspecified.
        // Array shapes with mixed keys serialize as a JSON object.
        if ((string) Type::arrayKey() === (string) $keyType && !$type instanceof ArrayShapeType) {
            $wrappedType = $type->getWrappedType();

            // A non-generic Traversable or ArrayAccess class arrives wrapped in a CollectionType without value type
            $this->describer->describe(
                $wrappedType instanceof Type\ObjectType ? $wrappedType : Type::list($type->getCollectionValueType()),
                $schema,
                $context
            );

            return;
        }

        $arrayTypes = array_map(
            static fn (Type $keyType): Type => Type::array($type->getCollectionValueType(), $keyType),
            $keyType->getTypes()
        );

        // Type::union() turns a single member into a nullable
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
}
