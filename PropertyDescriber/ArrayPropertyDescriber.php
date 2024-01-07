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
use Nelmio\ApiDocBundle\Exception\UndocumentedArrayItemsException;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;

class ArrayPropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    /** @var PropertyDescriberInterface */
    private $propertyDescriber;

    public function __construct(PropertyDescriberInterface $propertyDescriber)
    {
        $this->propertyDescriber = $propertyDescriber;
    }

    public function describe(array $types, OA\Schema $property, array $groups = null, ?OA\Schema $schema = null, array $context = [])
    {
        // BC layer for symfony < 5.3
        $type = method_exists($types[0], 'getCollectionValueTypes') ?
            ($types[0]->getCollectionValueTypes()[0] ?? null) :
            $types[0]->getCollectionValueType();
        if (null === $type) {
            throw new UndocumentedArrayItemsException();
        }

        $property->type = 'array';
        $property = Util::getChild($property, OA\Items::class);

        try {
            $this->propertyDescriber->describe([$type], $property, $groups, $schema, $context);
        } catch (UndocumentedArrayItemsException $e) {
            if (null !== $e->getClass()) {
                throw $e; // This exception is already complete
            }

            throw new UndocumentedArrayItemsException(null, sprintf('%s[]', $e->getPath()));
        }
    }

    public function supports(array $types, array $context = []): bool
    {
        return 1 === count($types) && $types[0]->isCollection();
    }
}
