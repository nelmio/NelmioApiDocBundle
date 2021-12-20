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
 *
 * @Serializer\ExclusionPolicy("all")
 */
class JMSChatUser
{
    /**
     * @Serializer\Type("integer")
     * @Serializer\Expose
     */
    private $id;

    /**
     * @Serializer\Type("Nelmio\ApiDocBundle\Tests\Functional\Entity\NestedGroup\JMSPicture")
     * @Serializer\Groups({"mini"})
     * @Serializer\Expose
     */
    private $picture;

    /**
     * @Serializer\Type("array<Nelmio\ApiDocBundle\Tests\Functional\Entity\NestedGroup\JMSPicture>")
     * @Serializer\Expose
     */
    private $allPictures;
}
