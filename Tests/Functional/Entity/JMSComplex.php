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
 * @Serializer\ExclusionPolicy("all")
 * @SWG\Definition(required={"id", "user"})
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
