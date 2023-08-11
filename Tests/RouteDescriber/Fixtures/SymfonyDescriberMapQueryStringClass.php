<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\RouteDescriber\Fixtures;

use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\SelfDescribingModelInterface;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use Symfony\Component\PropertyInfo\Type;

class SymfonyDescriberMapQueryStringClass implements SelfDescribingModelInterface
{
    public static function describe(Schema $schema, Model $model): void
    {
        $schema->title = 'SelfDescribingTitle';
        $schema->description = $model->getType()->getClassName();
        $schema->type = 'object';

        $schema->properties = [
            new Property([
                'property' => 'id',
                'type' => Type::BUILTIN_TYPE_INT,
                'nullable' => false,
            ]),
        ];
    }
}
