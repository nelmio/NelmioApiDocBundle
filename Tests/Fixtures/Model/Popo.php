<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Fixtures\Model;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class Popo
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    public $foo;

    public function getId()
    {
        return $this->id;
    }
}
