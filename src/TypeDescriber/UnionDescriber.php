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

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\UnionType;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * @implements TypeDescriberInterface<UnionType>
 *
 * @internal
 */
final class UnionDescriber implements TypeDescriberInterface, TypeDescriberAwareInterface
{
    use TypeDescriberAwareTrait;

    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        $innerTypes = array_values(array_filter($type->getTypes(), static function (Type $innerType) {
            return !$innerType->isIdentifiedBy(TypeIdentifier::NULL);
        }));

        // Ensure that union types of a single type are not described in oneOf
        if (1 === \count($innerTypes)) {
            $this->describer->describe($innerTypes[0], $schema, $context);

            return;
        }

        // Members can be described identically (e.g. `list<T>|array<T>`): keep a single one of each
        $weakContext = Util::createWeakContext($schema->_context);
        $childSchemas = [];
        $describedTypes = [];
        foreach ($innerTypes as $innerType) {
            $childSchema = new Schema([
                '_context' => $weakContext,
            ]);

            $this->describer->describe($innerType, $childSchema, $context);

            $childSchemas[$key = $childSchema->toJson()] ??= $childSchema;
            $describedTypes[$key] ??= $innerType;
        }

        if (1 === \count($childSchemas)) {
            $this->describer->describe(reset($describedTypes), $schema, $context);

            return;
        }

        if (Generator::UNDEFINED === $schema->oneOf) {
            $schema->oneOf = [];
        }

        array_push($schema->oneOf, ...array_values($childSchemas));
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof UnionType;
    }
}
