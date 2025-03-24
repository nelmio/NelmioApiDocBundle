<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\PropertyDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type;

final class ObjectPropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    /**
     * @param array<string, mixed> $context Context options for describing the property
     */
    public function describe(array $types, OA\Schema $property, array $context = []): void
    {
        $type = new Type(
            $types[0]->getBuiltinType(),
            false,
            $types[0]->getClassName(),
            $types[0]->isCollection(),
            $types[0]->getCollectionKeyTypes(),
            $types[0]->getCollectionValueTypes()[0] ?? null,
        ); // ignore nullable field

        if (null === $types[0]->getClassName()) {
            $property->type = 'object';
            $property->additionalProperties = true;

            return;
        }

        if ($types[0]->isNullable()) {
            $weakContext = Util::createWeakContext($property->_context);
            $schemas = [new OA\Schema(['ref' => $this->modelRegistry->register(new Model($type, serializationContext: $context)), '_context' => $weakContext])];
            $property->oneOf = $schemas;

            return;
        }

        $property->ref = $this->modelRegistry->register(new Model($type, serializationContext: $context));
    }

    public function supports(array $types, array $context = []): bool
    {
        return 1 === \count($types)
            && Type::BUILTIN_TYPE_OBJECT === $types[0]->getBuiltinType();
    }
}
