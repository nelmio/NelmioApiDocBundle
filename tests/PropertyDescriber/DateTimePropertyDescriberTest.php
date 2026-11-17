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

use Nelmio\ApiDocBundle\PropertyDescriber\DateTimePropertyDescriber;
use OpenApi\Attributes\Property;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type as LegacyType;

class DateTimePropertyDescriberTest extends TestCase
{
    private DateTimePropertyDescriber $describer;
    private Property $property;
    /**
     * @var LegacyType[]
     */
    private array $types;

    protected function setUp(): void
    {
        if (!class_exists(LegacyType::class)) {
            self::markTestSkipped(LegacyType::class.' class does not exist.');
        }
        $this->describer = new DateTimePropertyDescriber();
        $this->property = new Property();
        $this->types = [new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, \DateTimeImmutable::class)];
    }

    /**
     * @return array<string, array<LegacyType[]>>
     */
    public static function doesNotSupport(): array
    {
        if (!class_exists(LegacyType::class)) {
            return [
                'empty types' => [[]],
            ];
        }

        return [
            'empty types' => [[]],
            'non-object type' => [[new LegacyType(LegacyType::BUILTIN_TYPE_INT)]],
            'non-datetime object type' => [[new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, \stdClass::class)]],
        ];
    }

    /**
     * @param LegacyType[] $types
     */
    #[DataProvider('doesNotSupport')]
    public function testNonDateTimeTypesareNotSupported(array $types): void
    {
        self::assertFalse($this->describer->supports($types));
    }

    public function testDatetimeTypesAreSupported(): void
    {
        self::assertTrue($this->describer->supports($this->types));
    }

    public function testDescribeSetsTypeAndFormat(): void
    {
        $this->describer->describe($this->types, $this->property);

        self::assertSame('string', $this->property->type);
        self::assertSame('date-time', $this->property->format);
    }

    public function testDoesNotSetFormatIfAlreadyDefined(): void
    {
        $this->property->format = 'date';

        $this->describer->describe($this->types, $this->property);

        self::assertSame('string', $this->property->type);
        self::assertSame('date', $this->property->format, 'should not have changed format');
    }
}
