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

use EXSyst\Component\Swagger\Schema;
use Nelmio\ApiDocBundle\Model\Model;
use Symfony\Component\PropertyInfo\Type;

class ScalarModelDescriber implements ModelDescriberInterface
{
    private static $supportedTypes = [
        Type::BUILTIN_TYPE_INT => 'integer',
        Type::BUILTIN_TYPE_FLOAT => 'float',
        Type::BUILTIN_TYPE_STRING => 'string',
        Type::BUILTIN_TYPE_BOOL => 'boolean',
    ];

    public function describe(Model $model, Schema $schema)
    {
        $type = self::$supportedTypes[$model->getType()->getBuiltinType()];
        $schema->setType($type);
    }

    public function supports(Model $model): bool
    {
        return isset(self::$supportedTypes[$model->getType()->getBuiltinType()]);
    }
}
