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

class IntegerPropertyDescriber implements PropertyDescriberInterface
{
    /**
     * @param array<string, mixed> $context Context options for describing the property
     */
    public function describe(array $types, OA\Schema $property, array $context = [])
    {
        $property->type = 'integer';
    }

    public function supports(array $types, array $context = []): bool
    {
        return 1 === count($types) && Type::BUILTIN_TYPE_INT === $types[0]->getBuiltinType();
    }
}
