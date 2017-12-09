<?php


namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class VirtualProperty
 * @package Nelmio\ApiDocBundle\Tests\Functional\Entity
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
        $this->user->setEmail("dummy@test.com");
    }
}
