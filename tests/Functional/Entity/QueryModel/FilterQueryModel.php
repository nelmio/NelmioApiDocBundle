<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity\QueryModel;

final class FilterQueryModel
{
    public function __construct(
        public string|int $filter,
        public string $filterBy,
    ) {
    }
}
