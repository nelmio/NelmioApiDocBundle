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
     * @var mixed[]
     */
    private array $options;

    /**
     * @var mixed[]
     */
    private array $serializationContext;

    /**
     * @param string[]|null $groups
     * @param mixed[]       $options
     * @param mixed[]       $serializationContext
     */
    public function __construct(
        private LegacyType|Type $type,
        ?array $groups = null,
        array $options = [],
        array $serializationContext = []
    ) {
        if ($type instanceof LegacyType) {
            trigger_deprecation(
                'nelmio/api-doc-bundle',
                '5.X', // TODO
                'Using Symfony\Component\PropertyInfo\Type as type in %s is deprecated, use Symfony\Component\TypeInfo\Type instead.',
                __METHOD__
            );
        }

        $this->options = $options;
        $this->serializationContext = $serializationContext;
        if (null !== $groups) {
            $this->serializationContext['groups'] = $groups;
        }
    }

    /**
     * @deprecated use getTypeInfo() instead
     */
    public function getType(): LegacyType
    {
        if ($this->type instanceof Type) {
            throw new \LogicException('This method is deprecated and should not be used with Symfony\Component\TypeInfo\Type. Use getTypeInfo() instead.');
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
