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

use EXSyst\Component\Swagger\Schema;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Exception\UndocumentedArrayItemsException;
use Symfony\Component\PropertyInfo\Type;

class ArrayPropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    /** @var PropertyDescriberInterface[] */
    private $propertyDescribers;

    public function __construct(iterable $propertyDescribers = [])
    {
        $this->propertyDescribers = $propertyDescribers;
    }

    public function describe(Type $type, Schema $property, array $groups = null)
    {
        $type = $type->getCollectionValueType();
        if (null === $type) {
            throw new UndocumentedArrayItemsException();
        }

        $property->setType('array');
        $property = $property->getItems();

        foreach ($this->propertyDescribers as $propertyDescriber) {
            if ($propertyDescriber instanceof ModelRegistryAwareInterface) {
                $propertyDescriber->setModelRegistry($this->modelRegistry);
            }
            if ($propertyDescriber->supports($type)) {
                try {
                    $propertyDescriber->describe($type, $property, $groups);
                } catch (UndocumentedArrayItemsException $e) {
                    if (null !== $e->getClass()) {
                        throw $e; // This exception is already complete
                    }

                    throw new UndocumentedArrayItemsException(null, sprintf('%s[]', $e->getPath()));
                }

                break;
            }
        }
    }

    public function supports(Type $type): bool
    {
        return $type->isCollection();
    }
}
