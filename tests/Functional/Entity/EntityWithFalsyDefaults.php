<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

class EntityWithFalsyDefaults
{
    /** @var int */
    public $zero = 0;

    /** @var float */
    public $float = 0.0;

    /** @var string */
    public $empty = '';

    /** @var bool */
    public $false = false;

    /** @var string|null */
    public $nullString;

    /** @var string[] */
    public $array = [];
}
