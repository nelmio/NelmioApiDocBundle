<?php


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
        if ($types[0]->getBuiltinType() === Type::BUILTIN_TYPE_ARRAY) {
            $property->type = 'array';
            $property = Util::getChild($property, OA\Items::class);
        }

        $property->oneOf = $property->oneOf !== OA\UNDEFINED ? $property->oneOf : [];

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
        if (count($types) < 2) {
            return false;
        }

        $onlyArrays = false;
        $onlyObjects = false;
        /** @var Type $type */
        foreach ($types as $type) {
            if ($type->getBuiltinType() === Type::BUILTIN_TYPE_OBJECT) {
                $onlyObjects = true;
            } elseif ($type->getBuiltinType() === Type::BUILTIN_TYPE_ARRAY && $type->getCollectionValueType() === Type::BUILTIN_TYPE_OBJECT) {
                $onlyArrays = true;
            } else {
                return false;
            }
        }

        return ($onlyArrays && !$onlyObjects) || (!$onlyArrays && $onlyObjects);
    }


}
