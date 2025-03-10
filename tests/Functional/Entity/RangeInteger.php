<?php

declare(strict_types=1);

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

trait RangeIntegerTrait
{
    /**
     * @var int<1, 99>
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
     * @var int<1, 99>|null
     */
    public $nullableRangeInt;
}

class RangeInteger
{
    use RangeIntegerTrait;

    /**
     * @var positive-int
     */
    public $positiveInt;

    /**
     * @var negative-int
     */
    public $negativeInt;
}
