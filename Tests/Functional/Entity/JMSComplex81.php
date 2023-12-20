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
use OpenApi\Attributes as OA;

#[Serializer\ExclusionPolicy('all')]
#[OA\Schema(
    required: ['id', 'user'],
    properties: [
        new OA\Property(
            property: 'virtual',
            ref: new Model(type: JMSUser::class)
        ),
    ],
)]
class JMSComplex81
{
    #[Serializer\Type('integer')]
    #[Serializer\Expose]
    #[Serializer\Groups(['list'])]
    private $id;

    #[OA\Property(ref: new Model(type: JMSUser::class))]
    #[Serializer\Expose]
    #[Serializer\Groups(['details'])]
    #[Serializer\SerializedName('user')]
    private $User;

    #[Serializer\Type('string')]
    #[Serializer\Expose]
    #[Serializer\Groups(['list'])]
    private $name;

    #[Serializer\VirtualProperty]
    #[Serializer\Expose]
    #[Serializer\Groups(['list'])]
    #[OA\Property(ref: new Model(type: JMSUser::class))]
    public function getVirtualFriend()
    {
    }
}
