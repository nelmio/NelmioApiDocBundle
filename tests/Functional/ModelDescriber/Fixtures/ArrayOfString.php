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

class ArrayOfString
{
    public array $untypedArray;

    /**
     * @var array<string>
     */
    public array $arrayOfStrings;

    /**
     * @var list<string>
     */
    public array $listOfStrings;

    /**
     * @var string[]
     */
    public array $shortArrayOfStrings;
}
