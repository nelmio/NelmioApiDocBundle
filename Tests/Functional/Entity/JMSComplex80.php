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
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

/**
 * @Serializer\ExclusionPolicy("all")
 *
 * @OA\Schema(
 *     required={"id", "user"},
 *
 *     @OA\Property(property="virtual", ref=@Model(type=JMSUser::class))
 * )
 */
class JMSComplex80
{
    /**
     * @Serializer\Type("integer")
     *
     * @Serializer\Expose
     *
     * @Serializer\Groups({"list"})
     */
    private $id;

    /**
     * @OA\Property(ref=@Model(type=JMSUser::class))
     *
     * @Serializer\Expose
     *
     * @Serializer\Groups({"details"})
     *
     * @Serializer\SerializedName("user")
     */
    private $User;

    /**
     * @Serializer\Type("string")
     *
     * @Serializer\Expose
     *
     * @Serializer\Groups({"list"})
     */
    private $name;

    /**
     * @Serializer\VirtualProperty
     *
     * @Serializer\Expose
     *
     * @Serializer\Groups({"list"})
     *
     * @OA\Property(ref=@Model(type=JMSUser::class))
     */
    public function getVirtualFriend()
    {
    }
}
