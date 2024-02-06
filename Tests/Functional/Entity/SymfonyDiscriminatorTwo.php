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

class SymfonyDiscriminatorTwo extends SymfonyDiscriminator80 implements SymfonyDiscriminatorFileMapping
{
    /**
     * @var string
     */
    public $two;
}
