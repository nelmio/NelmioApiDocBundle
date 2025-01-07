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
use Symfony\Component\TypeInfo\TypeFactoryTrait;

/**
 * @implements TypeDescriberInterface<CollectionType>
 *
 * @experimental
 *
 * @internal
 */
final class ArrayDescriber implements TypeDescriberInterface, TypeDescriberAwareInterface
{
    use TypeDescriberAwareTrait;

    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        if (!$type->getCollectionKeyType() instanceof Type\CompositeTypeInterface) {
            return;
        }

        $collections = array_map(
            fn (Type $keyType): CollectionType => TypeFactoryTrait::collection($type->getWrappedType(), $type->getCollectionValueType(), $keyType),
            $type->getCollectionKeyType()->getTypes(),
        );

        if ($type->getCollectionKeyType() instanceof Type\UnionType) {
            $describeType = Type::union(...$collections);
        }

        if ($type->getCollectionKeyType() instanceof Type\IntersectionType) {
            $describeType = Type::intersection(...$collections);
        }

        if (!isset($describeType)) {
            return;
        }

        $this->describer->describe($describeType, $schema, $context);
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof CollectionType
            && $type->getCollectionKeyType() instanceof Type\CompositeTypeInterface;
    }
}
