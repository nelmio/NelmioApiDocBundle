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
     * @param string[]|null         $groups
     * @param mixed[]               $options
     * @param mixed[]               $serializationContext
     * @param non-empty-string|null $name                 An optional custom name for the generated schema
     */
    public function __construct(
        private Type $type,
        ?array $groups = null,
        private array $options = [],
        private array $serializationContext = [],
        public readonly ?string $name = null,
    ) {
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
        return md5(serialize([$this->type, $this->getSerializationContext(), $this->name]));
    }

    /**
     * @return mixed[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
