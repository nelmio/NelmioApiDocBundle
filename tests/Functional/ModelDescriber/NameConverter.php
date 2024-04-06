<?php

declare(strict_types=1);

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber;

use Symfony\Component\Serializer\NameConverter\AdvancedNameConverterInterface;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;

class NameConverter implements AdvancedNameConverterInterface
{
    /**
     * @var MetadataAwareNameConverter
     */
    private $inner;

    public function __construct(MetadataAwareNameConverter $inner)
    {
        $this->inner = $inner;
    }

    /**
     * @param array<mixed> $context
     */
    public function normalize(string $propertyName, ?string $class = null, ?string $format = null, array $context = []): string
    {
        if (!isset($context['secret_name_converter_value'])) {
            return $this->inner->normalize($propertyName, $class, $format, $context);
        }

        return 'name_converter_context_'.$propertyName;
    }

    /**
     * @param array<mixed> $context
     */
    public function denormalize(string $propertyName, ?string $class = null, ?string $format = null, array $context = []): string
    {
        throw new \RuntimeException('Was not expected to be called');
    }
}
