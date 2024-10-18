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
use Nelmio\ApiDocBundle\Model\Model;
use OpenApi\Annotations\Schema;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\Uid\AbstractUid;

/**
 * @implements SchemaDescriberInterface<ObjectType>
 *
 * @experimental
 */
final class ObjectClassDescriber implements SchemaDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        if (is_a($type->getClassName(), AbstractUid::class, true)) {
            $schema->type = 'string';
            $schema->format = 'uuid';

            return;
        }

        if (is_a($type->getClassName(), \DateTimeInterface::class, true)) {
            $schema->type = 'string';
            $schema->format = 'date-time';

            return;
        }

        $schema->ref = $this->modelRegistry->register(
            new Model(new LegacyType('object', false, $type->getClassName()), null, null, $context)
        );
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof ObjectType
            && $type->isA(TypeIdentifier::OBJECT);
    }
}
