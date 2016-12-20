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
