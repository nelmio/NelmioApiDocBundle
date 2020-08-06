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
     * @SWG\Property(description = "User id", readOnly = true, title = "userid", default = null)
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

    /**
     * @var \DateTimeInterface
     */
    private $dateAsInterface;

    public function setMoney(float $money)
    {
        $this->money = $money;
    }

    /**
     * @SWG\Property(example=1)
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

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

    public function setFriend(self $friend = null)
    {
    }

    public function setDummy(Dummy $dummy)
    {
    }

    public function setStatus(string $status)
    {
    }

    public function getDateAsInterface(): \DateTimeInterface
    {
        return $this->dateAsInterface;
    }

    public function setDateAsInterface(\DateTimeInterface $dateAsInterface)
    {
        $this->dateAsInterface = $dateAsInterface;
    }
}
