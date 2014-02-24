<?php

namespace Nelmio\ApiDocBundle\Tests\Fixtures\Model;

use JMS\Serializer\Annotation as JMS;

class DiscriminatorClass extends JmsWithDiscriminators
{
    /**
     * @JMS\Type("string");
     */
    public $bar;
}
