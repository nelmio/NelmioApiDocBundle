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

use Swagger\Annotations as SWG;

/**
 * @author Guilhem N. <egetick@gmail.com>
 */
class User
{
    /**
     * @var integer
     *
     * @SWG\Property(description = "User id", required = true, readOnly = true, title = "userid", example=1)
     */
    private $id;

    /**
     * @var string
     *
     * @SWG\Property(readOnly = false)
     */
    private $email;

    /**
     * @var int
     *
     * @SWG\Property(type = "string")
     */
    private $friendsNumber;

    /**
     * @var float
     */
    private $money;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var User[]
     */
    private $users;

    /**
     * @param float $money
     */
    public function setMoney(float $money)
    {
        $this->money = $money;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @param int $friendsNumber
     */
    public function setFriendsNumber(int $friendsNumber)
    {
        $this->friendsNumber = $friendsNumber;
    }

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
