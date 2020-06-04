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

class BooleanPropertyDescriber implements PropertyDescriberInterface
{
    public function describe(array $types, OA\Schema $property, array $groups = null)
    {
        $property->type = 'boolean';
    }

    public function supports(array $types): bool
    {
        return count($types) === 1 && $types[0]->getBuiltinType();
    }
}
