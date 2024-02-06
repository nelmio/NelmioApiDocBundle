<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity\QueryModel;

enum SortEnum: string
{
    case asc = 'asc';
    case desc = 'desc';
}
