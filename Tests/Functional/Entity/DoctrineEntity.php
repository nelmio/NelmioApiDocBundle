<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Functional\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Dummy;

class DoctrineEntity
{
    /**
     * @ORM\Column(type="array")
     * @var string[]
     */
    public $arrayProperty;

    /**
     * @ORM\Column(type="object")
     * @var Dummy
     */
    public $objectProperty;
}
