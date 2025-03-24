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
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type;

final class DictionaryPropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface, PropertyDescriberAwareInterface
{
    use ModelRegistryAwareTrait;
    use PropertyDescriberAwareTrait;

    /**
     * @param array<string, mixed> $context Context options for describing the property
     */
    public function describe(array $types, OA\Schema $property, array $context = []): void
    {
        $property->type = 'object';
        /** @var OA\AdditionalProperties $additionalProperties */
        $additionalProperties = Util::getChild($property, OA\AdditionalProperties::class);

        $this->propertyDescriber->describe($types[0]->getCollectionValueTypes(), $additionalProperties, $context);
    }

    public function supports(array $types, array $context = []): bool
    {
        return 1 === \count($types)
            && $types[0]->isCollection()
            && 1 === \count($types[0]->getCollectionKeyTypes())
            && Type::BUILTIN_TYPE_STRING === $types[0]->getCollectionKeyTypes()[0]->getBuiltinType();
    }
}
