<?php

namespace Nelmio\ApiDocBundle\Tests\Fixtures\Model;

use JMS\SerializerBundle\Annotation as JMS;

class JmsTest
{
    public $nothing;

    /**
     * @JMS\Type("string");
     */
    public $foo;

    /**
     * @JMS\Type("DateTime");
     * @JMS\ReadOnly
     */
    public $bar;

    /**
     * @JMS\Type("double");
     * @JMS\SerializedName("number");
     */
    public $baz;

    /**
     * @JMS\Type("array");
     */
    public $arr;

}
