<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\RouteDescriber\Fixtures;

use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\SelfDescribingModelInterface;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use OpenApi\Attributes as OA;

class DTO implements SelfDescribingModelInterface
{
    public const EXAMPLE_NAME = 'exampleName';
    public const DESCRIPTION = 'some description';

    public function __construct(
        public int $id,
        public string $name,
        public ?string $nullableName,
        #[OA\Property(
            example: self::EXAMPLE_NAME,
        )]
        public string $nameWithExample,
        #[OA\Property(
            description: self::DESCRIPTION,
        )]
        public string $nameWithDescription,
    ) {
    }

    public static function describe(Schema $schema, Model $model): void
    {
        $schema->type = 'object';
        $schema->required = self::getRequired();
        $schema->properties = self::getProperties();
    }

    /**
     * @return string[]
     */
    public static function getRequired(): array
    {
        return [
            'id',
            'name',
            'nameWithExample',
            'nameWithDescription',
        ];
    }

    /**
     * @return Property[]
     */
    public static function getProperties(): array
    {
        return [
            new Property([
                'property' => 'id',
                'type' => 'int',
            ]),
            new Property([
                'property' => 'name',
                'type' => 'string',
            ]),
            new Property([
                'property' => 'nullableName',
                'type' => 'string',
                'nullable' => true,
            ]),
            new Property([
                'property' => 'nameWithExample',
                'type' => 'string',
                'example' => self::EXAMPLE_NAME,
            ]),
            new Property([
                'property' => 'nameWithDescription',
                'type' => 'string',
                'description' => self::DESCRIPTION,
            ]),
        ];
    }
}
