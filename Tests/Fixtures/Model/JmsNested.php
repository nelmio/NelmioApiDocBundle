<?php
namespace Nelmio\ApiDocBundle\Tests\Fixtures\Model;

use JMS\SerializerBundle\Annotation as JMS;

class JmsNested
{

    /**
     * @JMS\Type("DateTime");
     * @JMS\ReadOnly
     */
    public $foo;

    /**
     * @JMS\Type("string");
     */
    public $bar;

    /**
     * Epic description.
     *
     * With multiple lines.
     *
     * @JMS\Type("array<integer>")
     */
    public $baz;

}
