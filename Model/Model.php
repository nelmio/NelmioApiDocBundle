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

    private $options;
    private $serializationContext;

    /**
     * @param string[]|null $groups
     */
    public function __construct(Type $type, array $groups = null, array $options = null, array $serializationContext = [])
    {
        $this->type = $type;
        $this->options = $options;
        $this->serializationContext = $serializationContext;
        if (null !== $groups) {
            $this->serializationContext['groups'] = $groups;
        }
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
        return $this->serializationContext['groups'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSerializationContext(): array
    {
        return $this->serializationContext;
    }

    public function getHash(): string
    {
        return md5(serialize([$this->type, $this->getSerializationContext()]));
    }

    /**
     * @return mixed[]|null
     */
    public function getOptions()
    {
        return $this->options;
    }
}
