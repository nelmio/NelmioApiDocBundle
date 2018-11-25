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
     * @var int
     *
     * @SWG\Property(description = "User id", readOnly = true, title = "userid", example=1, default = null)
     */
    private $id;

    /**
     * @SWG\Property(type="string", readOnly = false)
     */
    private $email;

    /**
     * @var string[]
     *
     * @SWG\Property(
     *     description = "User roles",
     *     title = "roles",
     *     example="[""ADMIN"",""SUPERUSER""]",
     *     default = {"user"},
     * )
     */
    private $roles;

    /**
     * @var int
     *
     * @SWG\Property(type = "string")
     */
    private $friendsNumber;

    /**
     * @var float
     * @SWG\Property(default = 0.0)
     */
    private $money;

    /**
     * @var \DateTime
     * @SWG\Property(property="creationDate")
     */
    private $createdAt;

    /**
     * @var User[]
     */
    private $users;

    /**
     * @var User|null
     */
    private $friend;

    /**
     * @var string
     *
     * @SWG\Property(enum = {"disabled", "enabled"})
     */
    private $status;

    public function setMoney(float $money): void
    {
        $this->money = $money;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function setFriendsNumber(int $friendsNumber): void
    {
        $this->friendsNumber = $friendsNumber;
    }

    public function setCreatedAt(\DateTime $createAt): void
    {
    }

    public function setUsers(array $users): void
    {
    }

    public function setFriend(?self $friend = null): void
    {
    }

    public function setDummy(Dummy $dummy): void
    {
    }

    public function setStatus(string $status): void
    {
    }
}
