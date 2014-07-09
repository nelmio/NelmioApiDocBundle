<?php

namespace Nelmio\ApiDocBundle\Tests\Fixtures\Model;

use JMS\Serializer\Annotation as JMS;

class JmsInline
{
    /**
     * @JMS\Type("string");
     */
    public $foo;

    /**
     * @JMS\Inline
     */
    public $inline;
}
