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

class ArrayShapeTypes
{
    /** @var array{name: string, age: int, email?: string} */
    public array $sealed;

    /** @var array{id: int, label: string, ...} */
    public array $unsealed;

    /** @var array{user: array{name: string, age: int}, active: bool} */
    public array $nested;

    /** @var array{0: string, 1: int} */
    public array $numericKeys;

    /** @var array{name: ?string, email: ?string} */
    public array $nullableValues;
}
