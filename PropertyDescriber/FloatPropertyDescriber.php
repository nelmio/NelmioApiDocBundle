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

use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type;

class FloatPropertyDescriber implements PropertyDescriberInterface
{
    public function describe(Type $type, OA\Schema $property, array $groups = null)
    {
        $property->type = 'number';
        $property->format = 'float';
    }

    public function supports(Type $type): bool
    {
        return Type::BUILTIN_TYPE_FLOAT === $type->getBuiltinType();
    }
}
