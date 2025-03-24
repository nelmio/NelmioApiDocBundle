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

interface X {
    public function getX(): string;
}

interface Y {
    public function getY(): string;
}

class ClassWithIntersection
{
    public X&Y $simpleClass;
}
