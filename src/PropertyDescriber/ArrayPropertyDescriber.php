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

final class ArrayPropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface, PropertyDescriberAwareInterface
{
    use ModelRegistryAwareTrait;
    use PropertyDescriberAwareTrait;

    /**
     * @param array<string, mixed> $context Context options for describing the property
     */
    public function describe(array $types, OA\Schema $property, array $context = []): void
    {
        $property->type = 'array';
        /** @var OA\Items $property */
        $property = Util::getChild($property, OA\Items::class);

        foreach ($types[0]->getCollectionValueTypes() as $type) {
            // Handle list pseudo type
            // https://symfony.com/doc/current/components/property_info.html#type-getcollectionkeytypes-type-getcollectionvaluetypes
            if ($this->supports([$type], $context) && [] === $type->getCollectionValueTypes()) {
                continue;
            }

            $this->propertyDescriber->describe([$type], $property, $context);
        }
    }

    public function supports(array $types, array $context = []): bool
    {
        if (1 !== \count($types) || !$types[0]->isCollection()) {
            return false;
        }

        if ([] === $types[0]->getCollectionKeyTypes()) {
            return true;
        }

        return 1 === \count($types[0]->getCollectionKeyTypes())
            && Type::BUILTIN_TYPE_INT === $types[0]->getCollectionKeyTypes()[0]->getBuiltinType();
    }
}
