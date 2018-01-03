<?php
/**
 * Created by PhpStorm.
 * User: Frisks
 * Date: 2/12/2017
 * Time: 1:18 PM
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class JMSComplex
 * @package Nelmio\ApiDocBundle\Tests\Functional\Entity
 * @Serializer\ExclusionPolicy("all")
 */
class JMSComplex
{

    /**
     * @Serializer\Type("integer")
     * @Serializer\Expose
     * @Serializer\Groups({"list"})
     */
    private $id;


    /**
     * @Serializer\Type("Nelmio\ApiDocBundle\Tests\Functional\Entity\JMSUser")
     * @Serializer\Expose
     * @Serializer\Groups({"details"})
     */
    private $user;

    /**
     * @Serializer\Type("string")
     * @Serializer\Expose
     * @Serializer\Groups({"list"})
     */
    private $name;

}
