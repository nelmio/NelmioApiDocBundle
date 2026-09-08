<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\TypeDescriber;

use Nelmio\ApiDocBundle\TypeDescriber\DateTimeDescriber;
use OpenApi\Annotations\Schema;
use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\ObjectType;

class DateTimeDescriberTest extends TestCase
{
    private DateTimeDescriber $describer;

    protected function setUp(): void
    {
        $this->describer = new DateTimeDescriber();
    }

    /**
     * @dataProvider provideSupportedTypes
     *
     * @param ObjectType $type
     */
    public function testSupports($type): void
    {
        self::assertTrue($this->describer->supports($type));
    }

    public static function provideSupportedTypes(): \Generator
    {
        yield 'immutable' => [Type::object(\DateTimeImmutable::class)];
        yield 'mutable' => [Type::object(\DateTime::class)];
        yield 'interface' => [Type::object(\DateTimeInterface::class)];
    }

    /**
     * @dataProvider provideUnsupportedTypes
     *
     * @param ObjectType $type
     */
    public function testDoesNotSupport($type): void
    {
        self::assertFalse($this->describer->supports($type));
    }

    public static function provideUnsupportedTypes(): \Generator
    {
        yield 'plain class' => [Type::object(\stdClass::class)];
        yield 'date interval' => [Type::object(\DateInterval::class)];
    }

    public function testDoesNotSupportNonObjectType(): void
    {
        /* @phpstan-ignore argument.type (the chain dispatches every type, not only object types) */
        self::assertFalse($this->describer->supports(Type::string()));
    }

    public function testDescribe(): void
    {
        $schema = new Schema([]);

        $this->describer->describe(Type::object(\DateTimeImmutable::class), $schema);

        self::assertSame('string', $schema->type);
        self::assertSame('date-time', $schema->format);
    }
}
