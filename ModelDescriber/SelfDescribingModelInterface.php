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

/**
 * A self-describing model is a model able to describe its own schema through a static method call.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
interface SelfDescribingModelInterface
{
    public static function describe(Schema $schema, Model $model): void;
}
