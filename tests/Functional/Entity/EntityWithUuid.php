<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

use Symfony\Component\Uid\Uuid;

class EntityWithUuid
{
    public Uuid $id;
    public string $name;

    public function __construct(string $name)
    {
        $this->id = Uuid::v1();
        $this->name = $name;
    }
}
