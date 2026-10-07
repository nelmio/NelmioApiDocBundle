<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\Fixtures\TypeInfo;

use Nelmio\ApiDocBundle\Tests\Functional\Entity\User;

class UnionOfIdenticalSchemas
{
    /**
     * @var array<int, string>|array<string>
     */
    public array $sameList;

    /**
     * @var list<User>|array<User>|null
     */
    public ?array $sameNullableListOfObjects;

    /**
     * @var \DateTime|\DateTimeImmutable
     */
    public \DateTimeInterface $sameDate;

    /**
     * @var list<int>|array<string>|array<int>
     */
    public array $partlySameLists;

    /**
     * @var array<int, string>|array<string, string>
     */
    public array $listOrDict;
}
