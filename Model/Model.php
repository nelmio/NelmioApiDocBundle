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

    private $groups;

    private $name;

    /**
     * Model constructor.
     *
     * @param Type        $type
     * @param array|null  $groups
     * @param string|null $definition
     */
    public function __construct(Type $type, array $groups = null, $name = null)
    {
        $this->type   = $type;
        $this->groups = $groups;
        $this->name   = $name;
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string[]|null
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return md5(serialize([$this->type, $this->groups, $this->name]));
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }
}
