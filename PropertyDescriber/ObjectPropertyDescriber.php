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
use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type;

class ObjectPropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    public function describe(array $types, OA\Schema $property, array $groups = null)
    {
        $type = new Type($types[0]->getBuiltinType(), false, $types[0]->getClassName(), $types[0]->isCollection(), $types[0]->getCollectionKeyType(), $types[0]->getCollectionValueType()); // ignore nullable field

        if ($types[0]->isNullable()) {
            $property->nullable = true;
            $property->allOf = [['$ref' => $this->modelRegistry->register(new Model($type, $groups))]];

            return;
        }

        $property->ref = $this->modelRegistry->register(new Model($type, $groups));
    }

    public function supports(array $types): bool
    {
        return 1 === count($types) && Type::BUILTIN_TYPE_OBJECT === $types[0]->getBuiltinType();
    }
}
