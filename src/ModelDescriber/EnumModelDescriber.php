<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\ModelDescriber;

use Nelmio\ApiDocBundle\Model\Model;
use OpenApi\Annotations\Schema;
use Symfony\Component\PropertyInfo\Type;

class EnumModelDescriber implements ModelDescriberInterface
{
    public const FORCE_NAMES = '_nelmio_enum_force_names';

    public function describe(Model $model, Schema $schema): void
    {
        $enumClass = $model->getType()->getClassName();
        $forceName = isset($model->getSerializationContext()[self::FORCE_NAMES]) && true === $model->getSerializationContext()[self::FORCE_NAMES];

        $enums = [];
        foreach ($enumClass::cases() as $enumCase) {
            $enums[] = $forceName ? $enumCase->name : $enumCase->value;
        }

        $reflectionEnum = new \ReflectionEnum($enumClass);
        if (!$forceName && $reflectionEnum->isBacked() && 'int' === $reflectionEnum->getBackingType()->getName()) {
            $schema->type = 'integer';
        } else {
            $schema->type = 'string';
        }
        $schema->enum = $enums;
    }

    public function supports(Model $model): bool
    {
        return Type::BUILTIN_TYPE_OBJECT === $model->getType()->getBuiltinType()
            && enum_exists($model->getType()->getClassName())
            && is_subclass_of($model->getType()->getClassName(), \BackedEnum::class);
    }
}
