<?php

namespace Nelmio\ApiDocBundle\Tests\Fixtures\Model;

use JMS\Serializer\Annotation as JMS;

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

    /**
     * @JMS\Type("Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested");
     */
    public $nested;

    /**
     * @JMS\Type("array<Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested>");
     */
    public $nestedArray;

    /**
     * @JMS\Groups("hidden")
     */
    public $hidden;
}
