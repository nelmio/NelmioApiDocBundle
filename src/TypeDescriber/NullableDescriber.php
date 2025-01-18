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

/**
 * @implements TypeDescriberInterface<Type>
 *
 * @internal
 */
final class NullableDescriber implements TypeDescriberInterface
{
    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        $schema->nullable = true;
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type->isNullable();
    }
}
