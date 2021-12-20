<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\NestedGroup;

use JMS\Serializer\Annotation as Serializer;

/**
 * User.
 */
#[Serializer\ExclusionPolicy('all')]
class JMSChatFriend
{
    #[Serializer\Type(\Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\NestedGroup\JMSChatRoom::class)]
    #[Serializer\Expose]
    #[Serializer\Groups(['mini'])]
    private $room;

    #[Serializer\Type(\Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\NestedGroup\JMSChatLivingRoom::class)]
    #[Serializer\Expose]
    #[Serializer\Groups(['Default', 'mini'])]
    private $living;

    #[Serializer\Type(\Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\NestedGroup\JMSChatRoom::class)]
    #[Serializer\Expose]
    private $dining;
}
