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

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\Uid\AbstractUid;

/**
 * @implements TypeDescriberInterface<ObjectType>
 *
 * @internal
 */
final class ClassDescriber implements TypeDescriberInterface, ModelRegistryAwareInterface
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

        // Ensure that the schema gets describe in oneOf for nullable objects
        if (true === $schema->nullable) {
            $weakContext = Util::createWeakContext($schema->_context);
            if (Generator::UNDEFINED === $schema->oneOf) {
                $schema->oneOf = [];
            }

            $schema = $schema->oneOf[] = new Schema([
                '_context' => $weakContext,
            ]);
        }

        $schema->ref = $this->modelRegistry->register(
            new Model(new LegacyType('object', false, $type->getClassName()), serializationContext: $context)
        );
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof ObjectType;
    }
}
