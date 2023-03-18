<?php

namespace Nelmio\ApiDocBundle\ModelDescriber;

use BackedEnum;
use Nelmio\ApiDocBundle\Model\Model;
use OpenApi\Annotations\Schema;
use Symfony\Component\PropertyInfo\Type;
use UnitEnum;

class EnumModelDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, Schema $schema): void
    {
        /** @var UnitEnum $enumClass */
        $enumClass = $model->getType()->getClassName();

        $enums = [];
        if (is_subclass_of($enumClass, BackedEnum::class)) {
            foreach ($enumClass::cases() as $enumCase) {
                $enums[] = $enumCase->value;
            }
            $type = isset($enums[0]) && is_int($enums[0]) ? 'integer' : 'string';
        } else {
            foreach ($enumClass::cases() as $enumCase) {
                $enums[] = $enumCase->name;
            }
            $type = 'string';
        }

        $schema->type = $type;
        $schema->enum = $enums;
    }

    public function supports(Model $model): bool
    {
        if (!function_exists('enum_exists')) {
            return false;
        }

        return Type::BUILTIN_TYPE_OBJECT === $model->getType()->getBuiltinType()
            && enum_exists($model->getType()->getClassName())
            && is_subclass_of($model->getType()->getClassName(), UnitEnum::class);
    }
}
