<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity\QueryModel;

class SortQueryModel
{
    public string $sortBy;
    public SortEnum $orderBy = SortEnum::asc;
}
