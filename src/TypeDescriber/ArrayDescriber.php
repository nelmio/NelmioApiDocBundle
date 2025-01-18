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

        $arrayTypes = array_map(
            fn (Type $keyType): Type => Type::array($type->getCollectionValueType(), $keyType),
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
}
