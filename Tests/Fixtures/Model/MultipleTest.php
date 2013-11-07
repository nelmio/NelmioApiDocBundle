<?php

namespace Nelmio\ApiDocBundle\Tests\Fixtures\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class MultipleTest
{
    public $nothing;

    /**
     * @Assert\Type("DateTime")
     */
    public $bar;

    /**
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("number");
     */
    public $baz;

    /**
     * @JMS\Type("Nelmio\ApiDocBundle\Tests\Fixtures\Model\Test")
     */
    public $related;

    /**
     * @Assert\Type(type="array")
     * @Assert\All({
     *     @Assert\Type(type="Test")
     * })
     */
    public $objects;
}
