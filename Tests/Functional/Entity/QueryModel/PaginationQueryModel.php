<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity\QueryModel;

final class PaginationQueryModel
{
    public int $offset = 0;
    public int $limit = 10;
}
