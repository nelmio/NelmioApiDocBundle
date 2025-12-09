<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber;

use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\TypeInfo\Type\ObjectType;

class VirtualTypeClassDoesNotExistsHandlerDefinedDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, OA\Schema $schema): void
    {
        $schema->type = 'object';
        $property = Util::getProperty($schema, 'custom_prop');
        $property->type = 'string';
    }

    public function supports(Model $model): bool
    {
        if (class_exists(LegacyType::class)) {
            return LegacyType::BUILTIN_TYPE_OBJECT === $model->getType()->getBuiltinType()
                && 'VirtualTypeClassDoesNotExistsHandlerDefined' === $model->getType()->getClassName();
        }

        return $model->getTypeInfo() instanceof ObjectType
            && 'VirtualTypeClassDoesNotExistsHandlerDefined' === $model->getTypeInfo()->getClassName();
    }
}
