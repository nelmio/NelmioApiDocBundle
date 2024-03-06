<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

class RangeInteger
{
    /**
     * @var int<0, 100>
     */
    public $rangeInt;

    /**
     * @var int<1, max>
     */
    public $minRangeInt;

    /**
     * @var int<min, 99>
     */
    public $maxRangeInt;

    /**
     * @var positive-int
     */
    public $positiveInt;

    /**
     * @var negative-int
     */
    public $negativeInt;

    /**
     * @var int<0, 100>|null
     */
    public $nullableRangeInt;
}
