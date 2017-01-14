<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Model;

use Symfony\Component\PropertyInfo\Type;

final class Model
{
    private $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    /**
     * @return Type|null
     */
    public function getType()
    {
        return $this->type;
    }

    public function getHash()
    {
        return md5(serialize($this->type));
    }
}
