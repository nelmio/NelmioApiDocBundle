<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber;

use Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\Fixtures\TypeInfo\StoppableDescriberValueObject;
use Nelmio\ApiDocBundle\TypeDescriber\StoppableTypeDescriberInterface;
use OpenApi\Annotations\Schema;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\ObjectType;

/**
 * @implements StoppableTypeDescriberInterface<ObjectType>
 */
class StoppableValueObjectDescriber implements StoppableTypeDescriberInterface
{
    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        $schema->type = 'string';
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof ObjectType
            && StoppableDescriberValueObject::class === $type->getClassName();
    }
}
