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
    private Type $type;

    /**
     * @var mixed[]
     */
    private array $options;

    /**
     * @var mixed[]
     */
    private array $serializationContext;

    /**
     * @param string[]|null $groups
     * @param mixed[]|null  $options
     * @param mixed[]       $serializationContext
     */
    public function __construct(Type $type, ?array $groups = null, ?array $options = [], array $serializationContext = [])
    {
        if (null === $options) {
            trigger_deprecation('nelmio/api-doc-bundle', '4.33.4', 'Passing null to the "$options" argument of "%s()" is deprecated, pass an empty array instead.', __METHOD__);
            $options = [];
        }

        $this->type = $type;
        $this->options = $options;
        $this->serializationContext = $serializationContext;
        if (null !== $groups) {
            $this->serializationContext['groups'] = $groups;
        }
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
     * @return mixed[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
