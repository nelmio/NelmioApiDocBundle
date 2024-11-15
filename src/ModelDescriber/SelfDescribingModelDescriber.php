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

class SelfDescribingModelDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, OA\Schema $schema): void
    {
        \call_user_func([$model->getType()->getClassName(), 'describe'], $schema, $model);
    }

    public function supports(Model $model): bool
    {
        return null !== $model->getType()->getClassName()
            && class_exists($model->getType()->getClassName())
            && is_a($model->getType()->getClassName(), SelfDescribingModelInterface::class, true);
    }
}
