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
use Swagger\Annotations\Definition;

interface ModelDescriberInterface
{
    public function describe(Model $model, Definition $definition);

    public function supports(Model $model): bool;
}
