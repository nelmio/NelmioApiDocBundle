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

class CompoundEntity
{
    /**
     * @var int|CompoundEntity[]
     */
    public $complex;

    /**
     * @var int|CompoundEntity[]|null
     */
    public $nullableComplex;

    /**
     * @var CompoundEntityNested[]|string|null
     */
    public $complexNested;

    /**
     * @var array<CompoundEntityNested>|array<array<CompoundEntityNested>>
     */
    public $arrayOfArrayComplex;
}
