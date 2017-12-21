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
 * Class VirtualProperty.
 *
 * @Serializer\ExclusionPolicy("all")
 * @Serializer\VirtualProperty(
 *     "email",
 *     exp="object.user.email",
 *     options={@Serializer\Type("string")}
 *  )
 */
class VirtualProperty
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\Expose
     */
    private $id;

    /**
     * @var User
     */
    private $user;

    public function __construct()
    {
        $this->user = new User();
        $this->user->setEmail('dummy@test.com');
    }
}
