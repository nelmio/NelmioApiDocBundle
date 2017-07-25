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

/**
 * @author Guilhem N. <egetick@gmail.com>
 */
class User
{
    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var User[]
     */
    private $users;

    public function setCreatedAt(\DateTime $createAt)
    {
    }

    public function setUsers(array $users)
    {
    }

    public function setDummy(Dummy $dummy)
    {
    }
}
