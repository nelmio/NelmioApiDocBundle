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

class ArrayPropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface, PropertyDescriberAwareInterface
{
    use ModelRegistryAwareTrait;
    use PropertyDescriberAwareTrait;

    /**
     * @param array<string, mixed> $context Context options for describing the property
     */
    public function describe(array $types, OA\Schema $property, ?array $groups = null, ?OA\Schema $schema = null, array $context = [])
    {
        if (null === $schema) {
            trigger_deprecation(
                'nelmio/api-doc-bundle',
                '4.15.0',
                '"%s()" will have a new "OA\Schema $schema" argument in a future version. Not defining it or passing null is deprecated',
                __METHOD__
            );
        }

        if (null !== $groups) {
            trigger_deprecation(
                'nelmio/api-doc-bundle',
                '4.17.0',
                'Using the $groups parameter of "%s()" is deprecated and will be removed in a future version. Pass groups via $context[\'groups\']',
                __METHOD__
            );
        }

        $property->type = 'array';
        /** @var OA\Items $property */
        $property = Util::getChild($property, OA\Items::class);

        foreach ($types[0]->getCollectionValueTypes() as $type) {
            // Handle list pseudo type
            // https://symfony.com/doc/current/components/property_info.html#type-getcollectionkeytypes-type-getcollectionvaluetypes
            if ($this->supports([$type]) && [] === $type->getCollectionValueTypes()) {
                continue;
            }

            $this->propertyDescriber->describe([$type], $property, $groups, $schema, $context);
        }
    }

    public function supports(array $types): bool
    {
        if (1 !== count($types) || !$types[0]->isCollection()) {
            return false;
        }

        if ([] === $types[0]->getCollectionKeyTypes()) {
            return true;
        }

        return 1 === count($types[0]->getCollectionKeyTypes())
            && Type::BUILTIN_TYPE_INT === $types[0]->getCollectionKeyTypes()[0]->getBuiltinType();
    }
}
