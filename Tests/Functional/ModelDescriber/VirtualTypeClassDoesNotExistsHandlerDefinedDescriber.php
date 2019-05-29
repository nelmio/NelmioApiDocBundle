<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber;

use EXSyst\Component\Swagger\Schema;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use Symfony\Component\PropertyInfo\Type;

class VirtualTypeClassDoesNotExistsHandlerDefinedDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, Schema $schema)
    {
        $schema->setType('object');
        $schema->getProperties()->get('custom_prop')->setType('string');
    }

    public function supports(Model $model): bool
    {
        return Type::BUILTIN_TYPE_OBJECT === $model->getType()->getBuiltinType()
            && 'VirtualTypeClassDoesNotExistsHandlerDefined' === $model->getType()->getClassName();
    }
}
