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
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * @implements StoppableTypeDescriberInterface<ObjectType>
 *
 * @internal
 */
final class TranslatableDescriber implements StoppableTypeDescriberInterface
{
    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        $schema->type = 'string';
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof ObjectType
            && is_a($type->getClassName(), TranslatableInterface::class, true)
            && !is_subclass_of($type->getClassName(), \BackedEnum::class);
    }
}
