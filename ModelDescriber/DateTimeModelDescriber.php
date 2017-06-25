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

class DateTimeModelDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, Schema $schema)
    {
        $schema->setType('string');
        $schema->setFormat('date-time');
    }

    public function supports(Model $model): bool
    {
        return 'DateTime' === $model->getType()->getClassName() || 'DateTimeImmutable' === $model->getType()->getClassName();
    }
}
