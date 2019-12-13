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
use Nelmio\ApiDocBundle\Model\Model;
use Symfony\Component\PropertyInfo\Type;

class ObjectPropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    public function describe(Type $type, Schema $property, array $groups = null)
    {
        $type = new Type($type->getBuiltinType(), false, $type->getClassName(), $type->isCollection(), $type->getCollectionKeyType(), $type->getCollectionValueType()); // ignore nullable field

        $property->setRef(
            $this->modelRegistry->register(new Model($type, $groups))
        );
    }

    public function supports(Type $type): bool
    {
        return Type::BUILTIN_TYPE_OBJECT === $type->getBuiltinType();
    }
}
