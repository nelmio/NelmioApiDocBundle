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
use Symfony\Component\TypeInfo\Type\IntersectionType;

/**
 * @implements SchemaDescriberInterface<IntersectionType>
 *
 * @experimental
 */
final class IntersectionDescriber implements SchemaDescriberInterface, SchemaDescriberAwareInterface
{
    use SchemaDescriberAwareTrait;

    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        $weakContext = Util::createWeakContext($schema->_context);

        $schema->allOf = Generator::UNDEFINED !== $schema->allOf ? $schema->allOf : [];
        foreach ($type->getTypes() as $innerType) {
            $schema->allOf[] = $childSchema = new Schema([
                '_context' => $weakContext
            ]);

            $this->describer->describe($innerType, $childSchema, $context);
        }
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof IntersectionType;
    }
}
