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

use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use OpenApi\Annotations\Schema;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * @implements SchemaDescriberInterface<Type\ObjectType>
 *
 * @experimental
 */
final class ObjectDescriber implements SchemaDescriberInterface
{
    private ModelRegistry $modelRegistry;

    public function __construct(ModelRegistry $modelRegistry)
    {
        $this->modelRegistry = $modelRegistry;
    }

    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        $schema->ref = $this->modelRegistry->register(new Model($type->getClassName(), null, null, $context));
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type->isA(TypeIdentifier::OBJECT);
    }
}
