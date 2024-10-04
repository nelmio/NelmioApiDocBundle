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
use Symfony\Component\Uid\AbstractUid;

final class UuidModelDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, Schema $schema)
    {
        $schema->type = 'string';
        $schema->format = 'uuid';
    }

    public function supports(Model $model): bool
    {
        return Type::BUILTIN_TYPE_OBJECT === $model->getType()->getBuiltinType()
            && is_a($model->getType()->getClassName(), AbstractUid::class, true);
    }
}
