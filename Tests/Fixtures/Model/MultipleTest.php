<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
