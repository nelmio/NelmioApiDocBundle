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
use OpenApi\Annotations as OA;
use OpenApi\Annotations\Schema;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * @implements TypeDescriberInterface<CollectionType>
 *
 * @internal
 */
final class DictionaryDescriber implements TypeDescriberInterface, TypeDescriberAwareInterface
{
    use TypeDescriberAwareTrait;

    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        $schema->type = 'object';
        $additionalProperties = Util::getChild($schema, OA\AdditionalProperties::class);

        $this->describer->describe($type->getCollectionValueType(), $additionalProperties, $context);
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof CollectionType
            && $type->getCollectionKeyType() instanceof Type\BuiltinType
            && TypeIdentifier::STRING === $type->getCollectionKeyType()->getTypeIdentifier();
    }
}
