<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\Fixtures;

use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV1;
use Symfony\Component\Uid\UuidV3;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Uid\UuidV5;
use Symfony\Component\Uid\UuidV6;

class UuidClass
{
    public Uuid $uuid;
    public Ulid $ulid;
    public UuidV1 $uuidV1;
    public UuidV3 $uuidV3;
    public UuidV4 $uuidV4;
    public UuidV5 $uuidV5;
    public UuidV6 $uuidV6;

    public ?Uuid $nullableUuid;
}
