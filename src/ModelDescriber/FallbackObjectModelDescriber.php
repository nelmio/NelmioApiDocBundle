<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\ModelDescriber;

use Nelmio\ApiDocBundle\Model\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\TypeInfo\Type\ObjectType;

class FallbackObjectModelDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, OA\Schema $schema): void
    {
    }

    public function supports(Model $model): bool
    {
        if (class_exists(\Symfony\Component\TypeInfo\Type::class)) {
            return $model->getTypeInfo() instanceof ObjectType;
        }

        return LegacyType::BUILTIN_TYPE_OBJECT === $model->getType()->getBuiltinType();
    }
}
