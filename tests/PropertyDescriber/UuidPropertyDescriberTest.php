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

use Nelmio\ApiDocBundle\PropertyDescriber\UuidPropertyDescriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Uid\Uuid;

class UuidPropertyDescriberTest extends TestCase
{
    public function testSupportsUuidPropertyType(): void
    {
        $type = new Type(Type::BUILTIN_TYPE_OBJECT, false, Uuid::class);

        $describer = new UuidPropertyDescriber();

        self::assertTrue($describer->supports([$type]));
    }

    public function testSupportsNoIntPropertyType(): void
    {
        $type = new Type(Type::BUILTIN_TYPE_INT, false);

        $describer = new UuidPropertyDescriber();

        self::assertFalse($describer->supports([$type]));
    }

    public function testSupportsNoDifferentObjectPropertyType(): void
    {
        $type = new Type(Type::BUILTIN_TYPE_OBJECT, false, \DateTimeInterface::class);

        $describer = new UuidPropertyDescriber();

        self::assertFalse($describer->supports([$type]));
    }

    public function testDescribeUuidPropertyType(): void
    {
        $property = $this->initProperty();
        $schema = $this->initSchema();

        $describer = new UuidPropertyDescriber();
        $describer->describe([], $property, [], $schema);

        self::assertSame('string', $property->type);
        self::assertSame('uuid', $property->format);
    }

    private function initProperty(): \OpenApi\Annotations\Property
    {
        if (PHP_VERSION_ID < 80000) {
            return new \OpenApi\Annotations\Property([]);
        }

        return new \OpenApi\Attributes\Property(); // union types, used in schema attribute require PHP >= 8.0.0
    }

    private function initSchema(): \OpenApi\Annotations\Schema
    {
        if (PHP_VERSION_ID < 80000) {
            return new \OpenApi\Annotations\Schema([]);
        }

        return new \OpenApi\Attributes\Schema(); // union types, used in schema attribute require PHP >= 8.0.0
    }
}
