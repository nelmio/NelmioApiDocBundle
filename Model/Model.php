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

    private $options;

    private $versions;

    /**
     * @param string[]|null $groups
     * @param string[]|null $versions
     */
    public function __construct(Type $type, array $groups = null, array $options = null, array $versions = null)
    {
        $this->type = $type;
        $this->groups = $groups;
        $this->options = $options;
        $this->versions = $versions;
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
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * @return string[]|null
     */
    public function getGroups()
    {
        return $this->groups;
    }

    public function getHash(): string
    {
        return md5(serialize([$this->type, $this->groups, $this->versions ?: null]));
    }

    /**
     * @return mixed[]|null
     */
    public function getOptions()
    {
        return $this->options;
    }
}
