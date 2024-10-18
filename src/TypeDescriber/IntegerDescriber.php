<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\TypeDescriber;

use OpenApi\Annotations\Schema;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * @implements TypeDescriberInterface<Type\BuiltinType>
 *
 * @experimental
 */
final class IntegerDescriber implements TypeDescriberInterface
{
    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        $schema->type = 'integer';
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof Type\BuiltinType
            && $type->isA(TypeIdentifier::INT);
    }
}
