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

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\UnionType;

/**
 * @implements SchemaDescriberInterface<UnionType>
 *
 * @experimental
 */
final class UnionDescriber implements SchemaDescriberInterface, SchemaDescriberAwareInterface
{
    use SchemaDescriberAwareTrait;

    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        $weakContext = Util::createWeakContext($schema->_context);

        $schema->oneOf = Generator::UNDEFINED !== $schema->oneOf ? $schema->oneOf : [];
        foreach ($type->getTypes() as $innerType) {
            $schema->oneOf[] = $childSchema = new Schema([
                '_context' => $weakContext
            ]);

            $this->describer->describe($innerType, $childSchema, $context);
        }
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof UnionType;
    }
}
