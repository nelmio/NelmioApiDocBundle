<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\PropertyDescriber;

use Symfony\Component\PropertyInfo\Type;
use EXSyst\Component\Swagger\Schema;

interface PropertyDescriberInterface
{
    public function describe(Type $type, Schema $property, array $groups);

    public function supports(Type $type): bool;
}