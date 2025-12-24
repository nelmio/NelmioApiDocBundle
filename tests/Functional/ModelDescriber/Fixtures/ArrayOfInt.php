<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\Fixtures;

class ArrayOfInt
{
    /** @phpstan-ignore-next-line This value type is missing on purpose */
    public array $untypedArray;

    /**
     * @var array<int>
     */
    public array $arrayOfIntegers;

    /**
     * @var list<int>
     */
    public array $listOfIntegers;

    /**
     * @var list<int>|null
     */
    public ?array $listOfIntegersNullable;
}
