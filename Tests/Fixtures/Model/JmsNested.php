<?php
namespace Nelmio\ApiDocBundle\Tests\Fixtures\Model;

use JMS\Serializer\Annotation as JMS;

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
    public $bar = 'baz';

    /**
     * Epic description.
     *
     * With multiple lines.
     *
     * @JMS\Type("array<integer>")
     */
    public $baz;

    /**
     * @JMS\Type("Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested");
     */
    public $circular;

    /**
     * @JMS\Type("Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsTest");
     */
    public $parent;

    /**
     * @Jms\Type("string")
     * @Jms\Since("0.2")
     */
    public $since;

    /**
     * @Jms\Type("string")
     * @Jms\Until("0.3")
     */
    public $until;

    /**
     * @Jms\Type("string")
     * @Jms\Since("0.4")
     * @Jms\Until("0.5")
     */
    public $sinceAndUntil;
}
