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

use JMS\Serializer\Annotation as Serializer;

/**
 * User.
 *
 * @Serializer\ExclusionPolicy("all")
 */
class JMSUser
{
    /**
     * @Serializer\Type("integer")
     * @Serializer\Expose
     */
    private $id;

    /**
     * @Serializer\Type("string")
     * @Serializer\Expose
     */
    private $email;

    /**
     * @Serializer\Type("array<string>")
     * @Serializer\Accessor(getter="getRoles", setter="setRoles")
     * @Serializer\Expose
     */
    private $roles;

    /**
     * @Serializer\Type("string")
     */
    private $password;

    /**
     * Ignored as the JMS serializer can't detect its type.
     *
     * @Serializer\Expose
     */
    private $createdAt;

    /**
     * @Serializer\Type("array<Nelmio\ApiDocBundle\Tests\Functional\Entity\User>")
     * @Serializer\Expose
     */
    private $friends;

    /**
     * @Serializer\Type(User::class)
     * @Serializer\Expose
     */
    private $bestFriend;

    public function setRoles($roles)
    {
    }

    public function getRoles()
    {
    }

    public function setDummy(Dummy $dummy)
    {
    }
}
