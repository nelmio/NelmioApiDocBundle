<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\EntityExcluded\Symfony7;

use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @author Guilhem N. <guilhem.niot@gmail.com>
 */
class SerializedNameEntity
{
    /**
     * @var string
     */
    #[SerializedName('notfoo')]
    public $foo;

    /**
     * Tests serialized name feature.
     */
    #[SerializedName('notwhatyouthink')]
    public function setBar(string $bar)
    {
    }
}
