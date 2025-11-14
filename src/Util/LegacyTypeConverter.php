<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Util;

use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * @internal
 *
 * @see \Symfony\Component\PropertyInfo\Util\LegacyTypeConverter
 * @deprecated can be removed when Symfony 6.4 support is dropped {@see PropertyInfoExtractorInterface::getType()}
 */
final class LegacyTypeConverter
{
    /**
     * @param LegacyType[] $legacyTypes
     */
    public static function toTypeInfoType(?array $legacyTypes): ?Type
    {
        if (null === $legacyTypes || [] === $legacyTypes) {
            return null;
        }

        $nullable = false;
        $types = [];

        foreach ($legacyTypes as $legacyType) {
            $types[] = match ($legacyType->getBuiltinType()) {
                LegacyType::BUILTIN_TYPE_INT => Type::int(),
                LegacyType::BUILTIN_TYPE_STRING => Type::string(),
                LegacyType::BUILTIN_TYPE_ARRAY => Type::array(self::toTypeInfoType($legacyType->getCollectionValueTypes()), self::toTypeInfoType($legacyType->getCollectionKeyTypes())),
                LegacyType::BUILTIN_TYPE_OBJECT => $legacyType->isCollection()
                    ? Type::collection(Type::object($legacyType->getClassName()), self::toTypeInfoType($legacyType->getCollectionValueTypes()), self::toTypeInfoType($legacyType->getCollectionKeyTypes()))
                    : Type::object($legacyType->getClassName()),
                default => throw new \LogicException('Unsupported LegacyType type: '.$legacyType->getBuiltinType().'.'),
            };

            if (LegacyType::BUILTIN_TYPE_NULL === $legacyType->getBuiltinType() || $legacyType->isNullable()) {
                $nullable = true;
            }
        }

        if (1 === \count($types)) {
            return $nullable ? Type::nullable($types[0]) : $types[0];
        }

        return $nullable ? Type::nullable(Type::union(...$types)) : Type::union(...$types);
    }

    public static function toLegacyType(Type $type): LegacyType
    {
        $nullable = false;

        if ($type instanceof Type\NullableType) {
            $nullable = true;
            $type = $type->getWrappedType();
        }

        if ($type instanceof Type\ObjectType) {
            return new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, $nullable, $type->getClassName());
        }

        if ($type instanceof Type\CollectionType) {
            if ($type->getCollectionKeyType() instanceof Type\UnionType) {
                $collectionKeyType = [
                    new LegacyType(LegacyType::BUILTIN_TYPE_INT, false),
                    new LegacyType(LegacyType::BUILTIN_TYPE_STRING, false),
                ];
            } else {
                $collectionKeyType = $type->getCollectionKeyType()->isIdentifiedBy(TypeIdentifier::INT)
                    ? [new LegacyType(LegacyType::BUILTIN_TYPE_INT, false)]
                    : [new LegacyType(LegacyType::BUILTIN_TYPE_STRING, false)];
            }

            if ($type->getWrappedType()->isIdentifiedBy(TypeIdentifier::OBJECT)) {
                return new LegacyType(
                    LegacyType::BUILTIN_TYPE_OBJECT,
                    $nullable,
                    $type->getWrappedType() instanceof Type\GenericType
                        ? $type->getWrappedType()->getWrappedType()->getClassName()
                        : $type->getWrappedType()->getClassName(),
                    true,
                    collectionKeyType: $collectionKeyType,
                    collectionValueType: self::toLegacyType($type->getCollectionValueType()),
                );
            }

            return new LegacyType(LegacyType::BUILTIN_TYPE_ARRAY, $nullable, collection: true, collectionKeyType: $collectionKeyType, collectionValueType: self::toLegacyType($type->getCollectionValueType()));
        }

        throw new \LogicException('Unsupported TypeInfo type: '.$type->__toString().'.');
    }

    public static function createType(string $type): LegacyType|Type
    {
        if (!class_exists(Type::class)) {
            if (str_ends_with($type, '[]')) {
                return new LegacyType('array', false, null, true, null, self::createType(substr($type, 0, -2)));
            }

            return new LegacyType('object', false, $type);
        }

        if (str_ends_with($type, '[]')) {
            return Type::list(self::createType(substr($type, 0, -2)));
        }

        return Type::object($type);
    }
}
