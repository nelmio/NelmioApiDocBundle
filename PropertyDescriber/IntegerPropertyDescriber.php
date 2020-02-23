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

use EXSyst\Component\Swagger\Schema;
use Symfony\Component\PropertyInfo\Type;

class IntegerPropertyDescriber implements PropertyDescriberInterface
{
    public function describe(Type $type, Schema $property, array $groups = null)
    {
        $property->setType('integer');
    }

    public function supports(Type $type): bool
    {
        return Type::BUILTIN_TYPE_INT === $type->getBuiltinType();
    }
}
