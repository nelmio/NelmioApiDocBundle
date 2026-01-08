<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\PropertyDescriber;

use Nelmio\ApiDocBundle\PropertyDescriber\TranslatablePropertyDescriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Contracts\Translation\TranslatableInterface;

class TranslatablePropertyDescriberTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(LegacyType::class)) {
            self::markTestSkipped('LegacyType class does not exist.');
        }
    }

    public function testSupportsTranslatablePropertyType(): void
    {
        $type = new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, TranslatableInterface::class);

        $describer = new TranslatablePropertyDescriber();

        self::assertTrue($describer->supports([$type]));
    }

    public function testSupportsNoIntPropertyType(): void
    {
        $type = new LegacyType(LegacyType::BUILTIN_TYPE_INT, false);

        $describer = new TranslatablePropertyDescriber();

        self::assertFalse($describer->supports([$type]));
    }

    public function testSupportsNoDifferentObjectPropertyType(): void
    {
        $type = new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, \DateTimeInterface::class);

        $describer = new TranslatablePropertyDescriber();

        self::assertFalse($describer->supports([$type]));
    }

    public function testDescribeTranslatablePropertyType(): void
    {
        $property = $this->initProperty();

        $describer = new TranslatablePropertyDescriber();
        $describer->describe([], $property, []);

        self::assertSame('string', $property->type);
    }

    private function initProperty(): \OpenApi\Annotations\Property
    {
        return new \OpenApi\Attributes\Property(); // union types, used in schema attribute require PHP >= 8.0.0
    }
}
