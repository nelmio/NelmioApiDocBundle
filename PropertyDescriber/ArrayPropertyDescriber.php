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
            throw new \LogicException(sprintf('Property "%s:%s" is an array, but its items type isn\'t specified. You can specify that by using the type `string[]` for instance or `@SWG\Property(type="array", @SWG\Items(type="string"))`.', $class, $propertyName));
        }

        $property->setType('array');
        $property = $property->getItems();

        foreach ($this->propertyDescribers as $propertyDescriber) {
            if ($propertyDescriber instanceof ModelRegistryAwareInterface) {
                $propertyDescriber->setModelRegistry($this->modelRegistry);
            }
            if ($propertyDescriber->supports($type)) {
                $propertyDescriber->describe($type, $property, $groups);

                break;
            }
        }
    }

    public function supports(Type $type): bool
    {
        return $type->isCollection();
    }
}
