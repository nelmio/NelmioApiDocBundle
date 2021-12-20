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
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class CustomDateTime extends \DateTime
{
    /**
     * @Serializer\Type("string")
     * @Serializer\Expose
     * @Serializer\SerializedName("format")
     */
    private $format;
}
