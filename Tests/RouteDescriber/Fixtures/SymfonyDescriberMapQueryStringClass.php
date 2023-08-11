<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\RouteDescriber\Fixtures;

use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\SelfDescribingModelInterface;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use Symfony\Component\PropertyInfo\Type;

final class SymfonyDescriberMapQueryStringClass implements SelfDescribingModelInterface
{
    public const SCHEMA = 'SymfonyDescriberMapQueryStringClass';
    public const TITLE = 'SelfDescribingTitle';
    public const TYPE = 'object';

    public static function describe(Schema $schema, Model $model): void
    {
        $schema->schema = self::SCHEMA;
        $schema->title = self::TITLE;
        $schema->description = $model->getType()->getClassName();
        $schema->type = self::TYPE;

        $schema->properties = self::getProperties();
    }

    /**
     * @return Property[]
     */
    public static function getProperties(): array
    {
        return [
            new Property([
                'property' => 'id',
                'type' => Type::BUILTIN_TYPE_INT,
                'nullable' => false,
                'default' => 123,
            ]),
        ];
    }
}
