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

class ObjectPropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    public function describe(array $types, OA\Schema $property, array $groups = null, ?OA\Schema $schema = null, array $context = [])
    {
        $type = new Type(
            $types[0]->getBuiltinType(),
            false,
            $types[0]->getClassName(),
            $types[0]->isCollection(),
            // BC layer for symfony < 5.3
            method_exists($types[0], 'getCollectionKeyTypes') ? $types[0]->getCollectionKeyTypes() : $types[0]->getCollectionKeyType(),
            method_exists($types[0], 'getCollectionValueTypes') ?
                ($types[0]->getCollectionValueTypes()[0] ?? null) :
                $types[0]->getCollectionValueType()
        ); // ignore nullable field

        if ($types[0]->isNullable()) {
            $weakContext = Util::createWeakContext($property->_context);
            $schemas = [new OA\Schema(['ref' => $this->modelRegistry->register(new Model($type, $groups, null, $context)), '_context' => $weakContext])];

            if (function_exists('enum_exists') && enum_exists($type->getClassName())) {
                $property->allOf = $schemas;
            } else {
                $property->oneOf = $schemas;
            }

            return;
        }

        $property->ref = $this->modelRegistry->register(new Model($type, $groups, null, $context));
    }

    public function supports(array $types): bool
    {
        return 1 === count($types)
            && Type::BUILTIN_TYPE_OBJECT === $types[0]->getBuiltinType();
    }
}
