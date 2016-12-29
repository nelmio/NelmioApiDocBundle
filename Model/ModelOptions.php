<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Model;

use Symfony\Component\PropertyInfo\Type;

final class ModelOptions
{
    private $type;

    /**
     * @return Type|null
     */
    public function getType()
    {
        return $this->type;
    }

    public function setType(Type $type)
    {
        $this->type = $type;
    }

    public function getHash()
    {
        return md5(serialize($this->type));
    }

    public function validate()
    {
        if (null === $this->type) {
            throw new \LogicException('The model type must be specified.');
        }
    }
}
