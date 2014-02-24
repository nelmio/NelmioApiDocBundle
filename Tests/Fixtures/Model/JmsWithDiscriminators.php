<?php

namespace Nelmio\ApiDocBundle\Tests\Fixtures\Model;

use JMS\Serializer\Annotation as JMS;
use JMS\Serializer\Annotation\Discriminator;

/**
 * @Discriminator(field = "type", map = {"type1": "Nelmio\ApiDocBundle\Tests\Fixtures\Model\DiscriminatorClass"})
 */
abstract class JmsWithDiscriminators
{
    /**
     * @JMS\Type("string");
     */
    public $foo;
}
