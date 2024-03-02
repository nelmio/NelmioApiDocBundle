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

/**
 * @author Guilhem N. <guilhem.niot@gmail.com>
 */
class PrivateProtectedExposure
{
    private $privateField;
    protected $protectedField;

    /**
     * @var string
     */
    public $publicField;

    protected function setProtected(string $thing)
    {
    }
}
