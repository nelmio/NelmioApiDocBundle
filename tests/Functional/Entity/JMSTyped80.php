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

class JMSTyped80
{
    /**
     * @Serializer\Type("integer")
     */
    private int $id;

    /**
     * @OA\Property(ref=@Model(type=JMSUser::class))
     *
     * @Serializer\SerializedName("user")
     */
    private JMSUser $User;

    /**
     * @Serializer\Type("string")
     */
    private ?string $name;

    /**
     * @Serializer\VirtualProperty
     *
     * @OA\Property(ref=@Model(type=JMSUser::class))
     */
    public function getVirtualFriend(): JMSUser
    {
        return new JMSUser();
    }
}
