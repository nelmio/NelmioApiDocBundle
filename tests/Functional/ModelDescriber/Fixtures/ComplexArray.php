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

class ComplexArray
{
    public array $untypedArray;

    /**
     * @var list<int>|array<string, float>
     */
    public array $listOrDict;
}
