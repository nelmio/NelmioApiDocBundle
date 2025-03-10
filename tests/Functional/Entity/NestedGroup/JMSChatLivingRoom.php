<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity\NestedGroup;

use JMS\Serializer\Annotation as Serializer;

/**
 * User.
 */
#[Serializer\ExclusionPolicy(policy: 'all')]
class JMSChatLivingRoom
{
    #[Serializer\Type(name: 'integer')]
    #[Serializer\Expose]
    private $id;
}
