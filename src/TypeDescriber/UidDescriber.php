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
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Ulid;

/**
 * @implements StoppableTypeDescriberInterface<ObjectType>
 *
 * @internal
 */
final class UidDescriber implements StoppableTypeDescriberInterface
{
    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        $schema->type = 'string';
        $schema->format = is_a($type->getClassName(), Ulid::class, true) ? 'ulid' : 'uuid';
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof ObjectType
            && is_a($type->getClassName(), AbstractUid::class, true);
    }
}
