<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\ModelDescriber\Fixtures;

use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\SelfDescribingModelInterface;
use OpenApi\Annotations\Schema;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\TypeInfo\Type\ObjectType;

class SelfDescribingModel implements SelfDescribingModelInterface
{
    public static function describe(Schema $schema, Model $model): void
    {
        $schema->title = 'SelfDescribingTitle';

        /** @var LegacyType|ObjectType $type */
        $type = class_exists(LegacyType::class)
            ? $model->getType()
            : $model->getTypeInfo();
        $schema->description = $type->getClassName();
    }
}
