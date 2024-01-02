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

use Nelmio\ApiDocBundle\Tests\Functional\TestKernel;
use OpenApi\Annotations as OA;

if (TestKernel::isAnnotationsAvailable()) {
    /**
     * @author Guilhem N. <egetick@gmail.com>
     */
    class User
    {
        /**
         * @var int
         *
         * @OA\Property(description = "User id", readOnly = true, title = "userid", default = null)
         */
        private $id;

        /**
         * @OA\Property(type="string", readOnly = false)
         */
        private $email;

        /**
         * User Roles Comment.
         *
         * @var string[]
         *
         * @OA\Property(
         *     description = "User roles",
         *     title = "roles",
         *     example="[""ADMIN"",""SUPERUSER""]",
         *     default = {"user"},
         * )
         */
        private $roles;

        /**
         * User Location.
         *
         * @OA\Property(type = "string")
         */
        private $location;

        /**
         * @var int
         *
         * @OA\Property(type = "string")
         */
        private $friendsNumber;

        /**
         * @var float
         *
         * @OA\Property(default = 0.0)
         */
        private $money;

        /**
         * @var \DateTime
         *
         * @OA\Property(property="creationDate")
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
         * @var User[]|null
         */
        private $friends;

        /**
         * @var string
         *
         * @OA\Property(enum = {"disabled", "enabled"})
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
         * @OA\Property(example=1)
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

        public function setLocation(string $location)
        {
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

        public function setFriend(?self $friend = null)
        {
        }

        public function setFriends(array $friends = [])
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
} else {
    /**
     * @author Guilhem N. <egetick@gmail.com>
     */
    class User
    {
        /**
         * @var int
         */
        #[\OpenApi\Attributes\Property(description: 'User id', readOnly: true, title: 'userid', default: null)]
        private $id;

        #[\OpenApi\Attributes\Property(type: 'string', readOnly: false)]
        private $email;

        /**
         * User Roles Comment.
         *
         * @var string[]
         */
        #[\OpenApi\Attributes\Property(description: 'User roles', title: 'roles', example: '["ADMIN","SUPERUSER"]', default: ['user'])]
        private $roles;

        /**
         * User Location.
         */
        #[\OpenApi\Attributes\Property(type: 'string')]
        private $location;

        /**
         * @var int
         */
        #[\OpenApi\Attributes\Property(type: 'string')]
        private $friendsNumber;

        /**
         * @var float
         */
        #[\OpenApi\Attributes\Property(default: 0.0)]
        private $money;

        /**
         * @var \DateTime
         */
        #[\OpenApi\Attributes\Property(property: 'creationDate')]
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
         * @var User[]|null
         */
        private $friends;

        /**
         * @var string
         */
        #[\OpenApi\Attributes\Property(enum: ['disabled', 'enabled'])]
        private $status;

        /**
         * @var \DateTimeInterface
         */
        private $dateAsInterface;

        public function setMoney(float $money)
        {
            $this->money = $money;
        }

        #[\OpenApi\Attributes\Property(example: 1)]
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

        public function setLocation(string $location)
        {
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

        public function setFriend(?self $friend = null)
        {
        }

        public function setFriends(array $friends = [])
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
}
