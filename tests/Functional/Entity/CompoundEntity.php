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
     * @var int|list<CompoundEntity>
     */
    public $complex;

    /**
     * @var int|list<CompoundEntity>|null
     */
    public $nullableComplex;

    /**
     * @var list<CompoundEntityNested>|string|null
     */
    public $complexNested;

    /**
     * @var list<CompoundEntityNested>|list<list<CompoundEntityNested>>
     */
    public $arrayOfArrayComplex;
}
