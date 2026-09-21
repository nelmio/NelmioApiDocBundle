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

use Nelmio\ApiDocBundle\TypeDescriber\UidDescriber;
use OpenApi\Annotations\Schema;
use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\UuidV4;

class UidDescriberTest extends TestCase
{
    private UidDescriber $describer;

    protected function setUp(): void
    {
        $this->describer = new UidDescriber();
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
        yield 'ulid' => [Type::object(Ulid::class)];
        yield 'uuid' => [Type::object(UuidV4::class)];
        yield 'abstract uid' => [Type::object(AbstractUid::class)];
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
        yield 'date time' => [Type::object(\DateTimeImmutable::class)];
    }

    public function testDoesNotSupportNonObjectType(): void
    {
        /* @phpstan-ignore argument.type (the chain dispatches every type, not only object types) */
        self::assertFalse($this->describer->supports(Type::string()));
    }

    /**
     * @dataProvider provideDescribedFormats
     *
     * @param ObjectType $type
     */
    public function testDescribe($type, string $expectedFormat): void
    {
        $schema = new Schema([]);

        $this->describer->describe($type, $schema);

        self::assertSame('string', $schema->type);
        self::assertSame($expectedFormat, $schema->format);
    }

    public static function provideDescribedFormats(): \Generator
    {
        yield 'ulid' => [Type::object(Ulid::class), 'ulid'];
        yield 'uuid' => [Type::object(UuidV4::class), 'uuid'];
    }
}
