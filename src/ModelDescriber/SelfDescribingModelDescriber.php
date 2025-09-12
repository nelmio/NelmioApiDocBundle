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
use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\TypeInfo\Type\ObjectType;

class SelfDescribingModelDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, OA\Schema $schema): void
    {
        /** @var ObjectType|LegacyType $type */
        $type = class_exists(\Symfony\Component\TypeInfo\Type::class)
            ? $model->getTypeInfo()
            : $model->getType();

        \call_user_func([$type->getClassName(), 'describe'], $schema, $model);
    }

    public function supports(Model $model): bool
    {
        if (class_exists(\Symfony\Component\TypeInfo\Type::class)) {
            return $model->getTypeInfo() instanceof ObjectType
                && class_exists($model->getTypeInfo()->getClassName())
                && is_a($model->getTypeInfo()->getClassName(), SelfDescribingModelInterface::class, true);
        }

        return null !== $model->getType()->getClassName()
            && class_exists($model->getType()->getClassName())
            && is_a($model->getType()->getClassName(), SelfDescribingModelInterface::class, true);
    }
}
