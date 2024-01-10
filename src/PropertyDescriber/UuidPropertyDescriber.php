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
use Symfony\Component\Uid\AbstractUid;

class UuidPropertyDescriber implements PropertyDescriberInterface
{
    use NullablePropertyTrait;

    public function describe(array $types, OA\Schema $property, array $groups = null, ?OA\Schema $schema = null)
    {
        $property->type = 'string';
        $property->format = 'uuid';
        $property->pattern = '^[0-9a-f]{8}-?[0-9a-f]{4}-?[0-9a-f]{4}-?[0-9a-f]{4}-?[0-9a-f]{12}$';
        $this->setNullableProperty($types[0], $property, $schema);
    }

    public function supports(array $types): bool
    {
        return 1 === count($types)
            && Type::BUILTIN_TYPE_OBJECT === $types[0]->getBuiltinType()
            && is_a($types[0]->getClassName(), AbstractUid::class, true);
    }
}
