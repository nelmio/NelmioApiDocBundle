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
    public int $zero = 0;

    public float $float = 0.0;

    public string $empty = '';

    public bool $false = false;

    public null|string $null = null;

    /**
     * @var string[]
     */
    public array $array = [];
}
