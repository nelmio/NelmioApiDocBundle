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

class CompoundPropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    public function describe(array $types, OA\Schema $property, array $groups = null)
    {
        if (Type::BUILTIN_TYPE_ARRAY === $types[0]->getBuiltinType()) {
            $property->type = 'array';
            $property = Util::getChild($property, OA\Items::class);
        }

        $property->oneOf = OA\UNDEFINED !== $property->oneOf ? $property->oneOf : [];

        foreach ($types as $type) {
            $ref = $this->modelRegistry->register(new Model(
                new Type($types[0]->getBuiltinType(), false, $type->getClassName(), $type->isCollection(), $type->getCollectionKeyType(), $type->getCollectionValueType()),
                $groups
            ));

            $property->oneOf[] = ['$ref' => $ref];
        }
    }

    public function supports(array $types): bool
    {
        if (2 < count($types)) {
            return false;
        }

        $onlyArrays = false;
        $onlyObjects = false;
        /** @var Type $type */
        foreach ($types as $type) {
            if (Type::BUILTIN_TYPE_OBJECT === $type->getBuiltinType()) {
                $onlyObjects = true;
            } elseif (Type::BUILTIN_TYPE_ARRAY === $type->getBuiltinType() && Type::BUILTIN_TYPE_OBJECT === $type->getCollectionValueType()) {
                $onlyArrays = true;
            } else {
                return false;
            }
        }

        return ($onlyArrays && !$onlyObjects) || (!$onlyArrays && $onlyObjects);
    }
}
