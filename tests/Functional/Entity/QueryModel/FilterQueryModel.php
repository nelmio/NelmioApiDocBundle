<?php

declare(strict_types=1);

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity\QueryModel;

final class FilterQueryModel
{
    public function __construct(
        public string|int $filter,
        public string $filterBy,
    ) {
    }
}
