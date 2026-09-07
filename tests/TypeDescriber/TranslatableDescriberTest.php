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

use Nelmio\ApiDocBundle\TypeDescriber\TranslatableDescriber;
use OpenApi\Annotations\Schema;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatableDescriberTest extends TestCase
{
    private TranslatableDescriber $describer;

    protected function setUp(): void
    {
        $this->describer = new TranslatableDescriber();
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
        yield 'interface' => [Type::object(TranslatableInterface::class)];
        yield 'message' => [Type::object(TranslatableMessage::class)];
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
        yield 'backed enum' => [Type::object(TranslatableStatus::class)];
        yield 'plain class' => [Type::object(\stdClass::class)];
    }

    public function testDoesNotSupportNonObjectType(): void
    {
        /* @phpstan-ignore argument.type (the chain dispatches every type, not only object types) */
        self::assertFalse($this->describer->supports(Type::string()));
    }

    public function testDescribe(): void
    {
        $schema = new Schema([]);

        $this->describer->describe(Type::object(TranslatableMessage::class), $schema);

        self::assertSame('string', $schema->type);
    }
}

enum TranslatableStatus: string implements TranslatableInterface
{
    case Foo = 'foo';
    case Bar = 'bar';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->value, locale: $locale);
    }
}
