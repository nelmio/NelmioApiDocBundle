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
    use NullablePropertyTrait;

    public function describe(array $types, OA\Schema $property, array $groups = null)
    {
        $property->type = 'boolean';
        $this->setNullableProperty($types[0], $property);
    }

    public function supports(array $types): bool
    {
        return 1 === count($types) && Type::BUILTIN_TYPE_BOOL === $types[0]->getBuiltinType();
    }
}
