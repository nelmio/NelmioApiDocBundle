<?php

namespace Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\Fixtures;

use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema(properties: [
    new Property(property: 'id', type: 'string', example: '8a8f8e8e-8e8e-8e8e-8e8e-8e8e8e8e8e8e'),
    new Property(property: 'name', type: 'string', example: 'Agency Name'),
    new Property(property: 'addedAt', type: 'integer', example: 1631610000),
])]
class Issue2286
{
    public function addedAt(): int
    {
        return 1;
    }
}