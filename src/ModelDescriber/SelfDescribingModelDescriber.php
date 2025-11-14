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
use Symfony\Component\TypeInfo\Type\ObjectType;

class SelfDescribingModelDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, OA\Schema $schema): void
    {
        $type = $model->getTypeInfo();
        if (!$type instanceof ObjectType) {
            return;
        }

        \call_user_func([$type->getClassName(), 'describe'], $schema, $model);
    }

    public function supports(Model $model): bool
    {
        return $model->getTypeInfo() instanceof ObjectType
            && class_exists($model->getTypeInfo()->getClassName())
            && is_a($model->getTypeInfo()->getClassName(), SelfDescribingModelInterface::class, true);
    }
}
