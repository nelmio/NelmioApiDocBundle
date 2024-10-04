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

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use OpenApi\Annotations\Schema;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * @implements SchemaDescriberInterface<BuiltinType>
 *
 * @experimental
 */
final class ObjectDescriber implements SchemaDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        $schema->type = 'object';
        $schema->additionalProperties = true;
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof BuiltinType
            && $type->isA(TypeIdentifier::OBJECT);
    }
}
