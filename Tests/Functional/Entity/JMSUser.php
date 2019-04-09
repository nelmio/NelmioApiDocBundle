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
use Swagger\Annotations as SWG;

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
     * @Serializer\Groups({"list"})
     *
     * @SWG\Property(description = "User id", readOnly = true, title = "userid", example=1, default = null)
     */
    private $id;

    /**
     * @Serializer\Type("int")
     * @Serializer\Expose
     * @Serializer\SerializedName("daysOnline")
     *
     * @SWG\Property(default = 0, minimum = 1, maximum = 300)
     */
    private $daysOnline;

    /**
     * @Serializer\Type("string")
     * @Serializer\Expose
     * @SWG\Property(readOnly = false)
     * @Serializer\Groups({"details"})
     */
    private $email;

    /**
     * @Serializer\Type("array<string>")
     * @Serializer\Accessor(getter="getRoles", setter="setRoles")
     * @Serializer\Expose
     *
     * @SWG\Property(default = {"user"}, description = "Roles list", example="[""ADMIN"",""SUPERUSER""]", title="roles")
     */
    private $roles;

    /**
     * @Serializer\Type("string")
     */
    private $password;

    /**
     * @SWG\Property(property="last_update", type="date")
     * @Serializer\Expose
     */
    private $updatedAt;

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
     * @Serializer\Type("array<string, Nelmio\ApiDocBundle\Tests\Functional\Entity\User>")
     * @Serializer\Expose
     */
    private $indexedFriends;

    /**
     * @Serializer\Type("array<string, DateTime>")
     * @Serializer\Expose
     */
    private $favoriteDates;

    /**
     * @Serializer\Type(Nelmio\ApiDocBundle\Tests\Functional\Entity\CustomDateTime::class)
     * @Serializer\Expose
     */
    private $customDate;

    /**
     * @Serializer\Type("integer")
     * @Serializer\Expose
     * @Serializer\SerializedName("friendsNumber")
     *
     * @SWG\Property(type = "string", minLength = 1, maxLength = 100)
     */
    private $friendsNumber;

    /**
     * @Serializer\Type(User::class)
     * @Serializer\Expose
     */
    private $bestFriend;

    /**
     * Whether this user is enabled or disabled.
     *
     * Only enabled users may be used in actions.
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Expose
     *
     * @SWG\Property(enum = {"disabled", "enabled"})
     */
    private $status;

    /**
     * JMS custom types handled via Custom Type Handlers.
     *
     * @var string
     * @Serializer\Type("VirtualTypeClassDoesNotExistsHandlerDefined")
     * @Serializer\Expose
     */
    private $virtualType1;

    /**
     * JMS custom types handled via Custom Type Handlers.
     *
     * @var string
     * @Serializer\Type("VirtualTypeClassDoesNotExistsHandlerNotDefined")
     * @Serializer\Expose
     */
    private $virtualType2;

    /**
     * @Serializer\Type("array<array<float>>")
     * @Serializer\Expose
     */
    private $latLonHistory;

    /**
     * @Serializer\Type("array<string, array>")
     * @Serializer\Expose
     */
    private $freeFormObject;

    /**
     * @Serializer\Type("array")
     * @Serializer\Expose
     */
    private $freeFormObjectWithoutType;

    /**
     * @Serializer\Type("array<string, array<string, DateTime>>")
     * @Serializer\Expose
     */
    private $deepObject;

    /**
     * @Serializer\Type("array<string, array<DateTime>>")
     * @Serializer\Expose
     */
    private $deepObjectWithItems;

    /**
     * @Serializer\Type("array<array<array<string, array>>>")
     * @Serializer\Expose
     */
    private $deepFreeFormObjectCollection;

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
