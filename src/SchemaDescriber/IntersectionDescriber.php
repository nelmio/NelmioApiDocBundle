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
use Symfony\Component\TypeInfo\TypeIdentifier;

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
        $innerTypes = array_values(array_filter($type->getTypes(), function (Type $innerType) {
            return !$innerType->isA(TypeIdentifier::NULL);
        }));

        // Ensure that non $ref schemas are not described in allOf
        if (1 === count($innerTypes) && !$type instanceof Type\ObjectType && !$type instanceof Type\EnumType) {
            $this->describer->describe($innerTypes[0], $schema, $context);

            return;
        }

        $weakContext = Util::createWeakContext($schema->_context);
        foreach ($innerTypes as $innerType) {
            if (Generator::UNDEFINED === $schema->allOf) {
                $schema->allOf = [];
            }

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
