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

use Nelmio\ApiDocBundle\Util\LegacyTypeConverter;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\TypeInfo\Type;

final class Model
{
    /**
     * @param string[]|null         $groups
     * @param mixed[]               $options
     * @param mixed[]               $serializationContext
     * @param non-empty-string|null $name                 An optional custom name for the generated schema
     */
    public function __construct(
        private LegacyType|Type $type,
        ?array $groups = null,
        private array $options = [],
        private array $serializationContext = [],
        public readonly ?string $name = null,
    ) {
        if ($type instanceof LegacyType) {
            trigger_deprecation(
                'nelmio/api-doc-bundle',
                '5.7.0',
                'Using Symfony\Component\PropertyInfo\Type as type in %s is deprecated, use Symfony\Component\TypeInfo\Type instead.',
                __METHOD__
            );
        }

        if (null !== $groups) {
            $this->serializationContext['groups'] = $groups;
        }
    }

    /**
     * @deprecated use {@see getTypeInfo()} instead
     */
    public function getType(): LegacyType
    {
        if ($this->type instanceof Type) {
            return LegacyTypeConverter::toLegacyType($this->type);
        }

        return $this->type;
    }

    public function getTypeInfo(): Type
    {
        if ($this->type instanceof Type) {
            return $this->type;
        }

        $converted = LegacyTypeConverter::toTypeInfoType([$this->type]);

        if (null === $converted) {
            throw new \LogicException('Could not convert legacy type to TypeInfo type.');
        }

        return $converted;
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
        $type = class_exists(Type::class)
            ? $this->getTypeInfo()->__toString()
            : $this->getType();

        return md5(serialize([$type, $this->getSerializationContext(), $this->name]));
    }

    /**
     * @return mixed[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
