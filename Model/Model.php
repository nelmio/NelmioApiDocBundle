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
    /**
     * @param string[]|null $groups
     */
    public function __construct(private Type $type, private ?array $groups = null, private ?array $options = null)
    {
    }

    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @return string[]|null
     */
    public function getGroups(): ?array
    {
        return $this->groups;
    }

    public function getHash(): string
    {
        return md5(serialize([$this->type, $this->groups]));
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }
}
