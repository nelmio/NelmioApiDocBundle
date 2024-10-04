<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\SchemaDescriber;

use OpenApi\Annotations\Schema;
use OpenApi\Generator;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * @implements SchemaDescriberInterface<Type\BuiltinType>
 *
 * @experimental
 */
final class MixedDescriber implements SchemaDescriberInterface
{
    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        $schema->type = Generator::UNDEFINED;
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type->isA(TypeIdentifier::MIXED);
    }
}
