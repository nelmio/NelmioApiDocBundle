<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\ModelDescriber\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Nelmio\ApiDocBundle\ModelDescriber\Annotations\SymfonyConstraintAnnotationReader;
use Nelmio\ApiDocBundle\Tests\Helper;
use Nelmio\ApiDocBundle\Tests\ModelDescriber\Annotations\Fixture as CustomAssert;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

class SymfonyConstraintAnnotationReaderTest extends TestCase
{
    /**
     * @var AnnotationReader|null
     */
    private $doctrineAnnotations;

    protected function setUp(): void
    {
        $this->doctrineAnnotations = class_exists(AnnotationReader::class) ? new AnnotationReader() : null;
    }

    public function testUpdatePropertyFix1283(): void
    {
        if (interface_exists(Reader::class) && Kernel::MAJOR_VERSION < 7) {
            $entity = new class() {
                /**
                 * @Assert\NotBlank()
                 *
                 * @Assert\Length(min = 1)
                 */
                public $property1;

                /**
                 * @Assert\NotBlank()
                 */
                public $property2;
            };
        } else {
            $entity = new class() {
                #[Assert\Length(min: 1)]
                #[Assert\NotBlank()]
                public $property1;

                #[Assert\NotBlank()]
                public $property2;
            };
        }

        $schema = $this->createObj(OA\Schema::class, []);
        $schema->merge([$this->createObj(OA\Property::class, ['property' => 'property1'])]);
        $schema->merge([$this->createObj(OA\Property::class, ['property' => 'property2'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader($this->doctrineAnnotations);
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);
        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property2'), $schema->properties[1]);

        // expect required to be numeric array with sequential keys (not [0 => ..., 2 => ...])
        self::assertEquals($schema->required, ['property1', 'property2']);
    }

    /**
     * @param object $entity
     *
     * @dataProvider provideOptionalProperty
     */
    public function testOptionalProperty($entity): void
    {
        if (!\property_exists(Assert\NotBlank::class, 'allowNull')) {
            self::markTestSkipped('NotBlank::allowNull was added in symfony/validator 4.3.');
        }

        $schema = $this->createObj(OA\Schema::class, []);
        $schema->merge([$this->createObj(OA\Property::class, ['property' => 'property1'])]);
        $schema->merge([$this->createObj(OA\Property::class, ['property' => 'property2'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader($this->doctrineAnnotations);
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);
        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property2'), $schema->properties[1]);

        // expect required to be numeric array with sequential keys (not [0 => ..., 2 => ...])
        self::assertEquals($schema->required, ['property2']);
    }

    public static function provideOptionalProperty(): \Generator
    {
        if (interface_exists(Reader::class) && Kernel::MAJOR_VERSION < 7) {
            yield 'Annotations' => [
                new class() {
                    /**
                     * @Assert\NotBlank(allowNull = true)
                     *
                     * @Assert\Length(min = 1)
                     */
                    public $property1;

                    /**
                     * @Assert\NotBlank()
                     */
                    public $property2;
                },
            ];
        }

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\NotBlank(allowNull: true)]
                #[Assert\Length(min: 1)]
                public $property1;
                #[Assert\NotBlank]
                public $property2;
            }];
        }
    }

    /**
     * @param object $entity
     *
     * @dataProvider provideAssertChoiceResultsInNumericArray
     */
    public function testAssertChoiceResultsInNumericArray($entity): void
    {
        $schema = $this->createObj(OA\Schema::class, []);
        $schema->merge([$this->createObj(OA\Property::class, ['property' => 'property1'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader($this->doctrineAnnotations);
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);

        // expect enum to be numeric array with sequential keys (not [1 => "active", 2 => "active"])
        self::assertEquals($schema->properties[0]->enum, ['active', 'blocked']);
    }

    public static function provideAssertChoiceResultsInNumericArray(): \Generator
    {
        define('TEST_ASSERT_CHOICE_STATUSES', [
            1 => 'active',
            2 => 'blocked',
        ]);

        if (interface_exists(Reader::class) && Kernel::MAJOR_VERSION < 7) {
            yield 'Annotations' => [
                new class() {
                    /**
                     * @Assert\Length(min = 1)
                     *
                     * @Assert\Choice(choices=TEST_ASSERT_CHOICE_STATUSES)
                     */
                    public $property1;
                },
            ];
        }

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\Length(min: 1)]
                #[Assert\Choice(choices: TEST_ASSERT_CHOICE_STATUSES)]
                public $property1;
            }];
        }
    }

    /**
     * @param object $entity
     *
     * @dataProvider provideMultipleChoiceConstraintsApplyEnumToItems
     */
    public function testMultipleChoiceConstraintsApplyEnumToItems($entity): void
    {
        $schema = $this->createObj(OA\Schema::class, []);
        $schema->merge([$this->createObj(OA\Property::class, ['property' => 'property1'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader($this->doctrineAnnotations);
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);

        self::assertInstanceOf(OA\Items::class, $schema->properties[0]->items);
        self::assertEquals($schema->properties[0]->items->enum, ['one', 'two']);
    }

    public static function provideMultipleChoiceConstraintsApplyEnumToItems(): \Generator
    {
        if (interface_exists(Reader::class) && Kernel::MAJOR_VERSION < 7) {
            yield 'Annotations' => [new class() {
                /**
                 * @Assert\Choice(choices={"one", "two"}, multiple=true)
                 */
                public $property1;
            }];
        }

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\Choice(choices: ['one', 'two'], multiple: true)]
                public $property1;
            }];
        }
    }

    /**
     * @param object $entity
     *
     * @group https://github.com/nelmio/NelmioApiDocBundle/issues/1780
     *
     * @dataProvider provideLengthConstraintDoesNotSetMaxLengthIfMaxIsNotSet
     */
    public function testLengthConstraintDoesNotSetMaxLengthIfMaxIsNotSet($entity): void
    {
        $schema = $this->createObj(OA\Schema::class, []);
        $schema->merge([$this->createObj(OA\Property::class, ['property' => 'property1'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader($this->doctrineAnnotations);
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);

        self::assertSame(Generator::UNDEFINED, $schema->properties[0]->maxLength);
        self::assertSame(1, $schema->properties[0]->minLength);
    }

    public static function provideLengthConstraintDoesNotSetMaxLengthIfMaxIsNotSet(): \Generator
    {
        if (interface_exists(Reader::class) && Kernel::MAJOR_VERSION < 7) {
            yield 'Annotations' => [
                new class() {
                    /**
                     * @Assert\Length(min = 1)
                     */
                    public $property1;
                },
            ];
        }

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\Length(min: 1)]
                public $property1;
            }];
        }
    }

    /**
     * @param object $entity
     *
     * @group https://github.com/nelmio/NelmioApiDocBundle/issues/1780
     *
     * @dataProvider provideLengthConstraintDoesNotSetMinLengthIfMinIsNotSet
     */
    public function testLengthConstraintDoesNotSetMinLengthIfMinIsNotSet($entity): void
    {
        $schema = $this->createObj(OA\Schema::class, []);
        $schema->merge([$this->createObj(OA\Property::class, ['property' => 'property1'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader($this->doctrineAnnotations);
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);

        self::assertSame(Generator::UNDEFINED, $schema->properties[0]->minLength);
        self::assertSame(100, $schema->properties[0]->maxLength);
    }

    public static function provideLengthConstraintDoesNotSetMinLengthIfMinIsNotSet(): \Generator
    {
        if (interface_exists(Reader::class) && Kernel::MAJOR_VERSION < 7) {
            yield 'Annotations' => [
                new class() {
                    /**
                     * @Assert\Length(max = 100)
                     */
                    public $property1;
                },
            ];
        }

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\Length(max: 100)]
                public $property1;
            }];
        }
    }

    public function testCompoundValidationRules(): void
    {
        if (interface_exists(Reader::class) && Kernel::MAJOR_VERSION < 7) {
            $entity = new class() {
                /**
                 * @CustomAssert\CompoundValidationRule()
                 */
                public $property1;
            };
        } else {
            $entity = new class() {
                #[CustomAssert\CompoundValidationRule()]
                public $property1;
            };
        }
        $propertyName = 'property1';

        $schema = $this->createObj(OA\Schema::class, []);
        $schema->merge([$this->createObj(OA\Property::class, ['property' => $propertyName])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader($this->doctrineAnnotations);
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, $propertyName), $schema->properties[0]);

        if (Helper::isCompoundValidatorConstraintSupported()) {
            self::assertSame([$propertyName], $schema->required);
            self::assertSame(0, $schema->properties[0]->minimum);
            self::assertTrue($schema->properties[0]->exclusiveMinimum);
            self::assertSame(5, $schema->properties[0]->maximum);
            self::assertTrue($schema->properties[0]->exclusiveMaximum);
        } else {
            self::assertSame(Generator::UNDEFINED, $schema->required);
            self::assertSame(Generator::UNDEFINED, $schema->properties[0]->minimum);
            self::assertSame(Generator::UNDEFINED, $schema->properties[0]->exclusiveMinimum);
            self::assertSame(Generator::UNDEFINED, $schema->properties[0]->maximum);
            self::assertSame(Generator::UNDEFINED, $schema->properties[0]->exclusiveMaximum);
        }
    }

    /**
     * @param object $entity
     *
     * @group https://github.com/nelmio/NelmioApiDocBundle/issues/1821
     *
     * @dataProvider provideCountConstraintDoesNotSetMinItemsIfMinIsNotSet
     */
    public function testCountConstraintDoesNotSetMinItemsIfMinIsNotSet($entity): void
    {
        $schema = $this->createObj(OA\Schema::class, []);
        $schema->merge([$this->createObj(OA\Property::class, ['property' => 'property1'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader($this->doctrineAnnotations);
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);

        self::assertSame(Generator::UNDEFINED, $schema->properties[0]->minItems);
        self::assertSame(10, $schema->properties[0]->maxItems);
    }

    public static function provideCountConstraintDoesNotSetMinItemsIfMinIsNotSet(): \Generator
    {
        if (interface_exists(Reader::class) && Kernel::MAJOR_VERSION < 7) {
            yield 'Annotations' => [
                new class() {
                    /**
                     * @Assert\Count(max = 10)
                     */
                    public $property1;
                },
            ];
        }

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\Count(max: 10)]
                public $property1;
            }];
        }
    }

    /**
     * @param object $entity
     *
     * @group https://github.com/nelmio/NelmioApiDocBundle/issues/1821
     *
     * @dataProvider provideCountConstraintDoesNotSetMaxItemsIfMaxIsNotSet
     */
    public function testCountConstraintDoesNotSetMaxItemsIfMaxIsNotSet($entity): void
    {
        $schema = $this->createObj(OA\Schema::class, []);
        $schema->merge([$this->createObj(OA\Property::class, ['property' => 'property1'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader($this->doctrineAnnotations);
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);

        self::assertSame(Generator::UNDEFINED, $schema->properties[0]->maxItems);
        self::assertSame(10, $schema->properties[0]->minItems);
    }

    public static function provideCountConstraintDoesNotSetMaxItemsIfMaxIsNotSet(): \Generator
    {
        if (interface_exists(Reader::class) && Kernel::MAJOR_VERSION < 7) {
            yield 'Annotations' => [
                new class() {
                    /**
                     * @Assert\Count(min = 10)
                     */
                    public $property1;
                },
            ];
        }

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\Count(min: 10)]
                public $property1;
            }];
        }
    }

    /**
     * @param object $entity
     *
     * @group https://github.com/nelmio/NelmioApiDocBundle/issues/1822
     *
     * @dataProvider provideRangeConstraintDoesNotSetMaximumIfMaxIsNotSet
     */
    public function testRangeConstraintDoesNotSetMaximumIfMaxIsNotSet($entity): void
    {
        $schema = $this->createObj(OA\Schema::class, []);
        $schema->merge([$this->createObj(OA\Property::class, ['property' => 'property1'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader($this->doctrineAnnotations);
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);

        self::assertSame(Generator::UNDEFINED, $schema->properties[0]->maximum);
        self::assertSame(10, $schema->properties[0]->minimum);
    }

    public static function provideRangeConstraintDoesNotSetMaximumIfMaxIsNotSet(): \Generator
    {
        if (interface_exists(Reader::class) && Kernel::MAJOR_VERSION < 7) {
            yield 'Annotations' => [
                new class() {
                    /**
                     * @Assert\Range(min = 10)
                     */
                    public $property1;
                },
            ];
        }

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\Range(min: 10)]
                public $property1;
            }];
        }
    }

    /**
     * @param object $entity
     *
     * @group https://github.com/nelmio/NelmioApiDocBundle/issues/1822
     *
     * @dataProvider provideRangeConstraintDoesNotSetMinimumIfMinIsNotSet
     */
    public function testRangeConstraintDoesNotSetMinimumIfMinIsNotSet($entity): void
    {
        $schema = $this->createObj(OA\Schema::class, []);
        $schema->merge([$this->createObj(OA\Property::class, ['property' => 'property1'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader($this->doctrineAnnotations);
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);

        self::assertSame(Generator::UNDEFINED, $schema->properties[0]->minimum);
        self::assertSame(10, $schema->properties[0]->maximum);
    }

    public static function provideRangeConstraintDoesNotSetMinimumIfMinIsNotSet(): \Generator
    {
        if (interface_exists(Reader::class) && Kernel::MAJOR_VERSION < 7) {
            yield 'Annotations' => [
                new class() {
                    /**
                     * @Assert\Range(max = 10)
                     */
                    public $property1;
                },
            ];
        }

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\Range(max: 10)]
                public $property1;
            }];
        }
    }

    /**
     * re-using another provider here, since all constraints land in the default
     * group when `group={"someGroup"}` is not set.
     *
     * @group https://github.com/nelmio/NelmioApiDocBundle/issues/1857
     *
     * @dataProvider provideRangeConstraintDoesNotSetMinimumIfMinIsNotSet
     */
    public function testReaderWithValidationGroupsEnabledChecksForDefaultGroupWhenNoSerializationGroupsArePassed(object $entity): void
    {
        $schema = $this->createObj(OA\Schema::class, []);
        $schema->merge([$this->createObj(OA\Property::class, ['property' => 'property1'])]);
        $reader = $this->createConstraintReaderWithValidationGroupsEnabled();
        $reader->setSchema($schema);

        // no serialization groups passed here
        $reader->updateProperty(
            new \ReflectionProperty($entity, 'property1'),
            $schema->properties[0]
        );

        self::assertSame(10, $schema->properties[0]->maximum, 'should have read constraints in the default group');
    }

    /**
     * @group https://github.com/nelmio/NelmioApiDocBundle/issues/1857
     *
     * @dataProvider provideConstraintsWithGroups
     */
    public function testReaderWithValidationGroupsEnabledDoesNotReadAnnotationsWithoutDefaultGroupIfNoGroupsArePassed(object $entity): void
    {
        $schema = $this->createObj(OA\Schema::class, []);
        $schema->merge([
            $this->createObj(OA\Property::class, ['property' => 'property1']),
        ]);
        $reader = $this->createConstraintReaderWithValidationGroupsEnabled();
        $reader->setSchema($schema);

        // no serialization groups passed here
        $reader->updateProperty(
            new \ReflectionProperty($entity, 'property1'),
            $schema->properties[0]
        );

        self::assertSame(['property1'], $schema->required, 'should have read constraint in default group');
        self::assertSame(Generator::UNDEFINED, $schema->properties[0]->minimum, 'should not have read constraint in other group');
    }

    /**
     * @group https://github.com/nelmio/NelmioApiDocBundle/issues/1857
     *
     * @dataProvider provideConstraintsWithGroups
     */
    public function testReaderWithValidationGroupsEnabledReadsOnlyConstraintsWithGroupsProvided(object $entity): void
    {
        $schema = $this->createObj(OA\Schema::class, []);
        $schema->merge([
            $this->createObj(OA\Property::class, ['property' => 'property1']),
        ]);
        $reader = $this->createConstraintReaderWithValidationGroupsEnabled();
        $reader->setSchema($schema);

        // no serialization groups passed here
        $reader->updateProperty(
            new \ReflectionProperty($entity, 'property1'),
            $schema->properties[0],
            ['other']
        );

        self::assertSame(Generator::UNDEFINED, $schema->required, 'should not have read constraint in default group');
        self::assertSame(1, $schema->properties[0]->minimum, 'should have read constraint in other group');
    }

    /**
     * @group https://github.com/nelmio/NelmioApiDocBundle/issues/1857
     *
     * @dataProvider provideConstraintsWithGroups
     */
    public function testReaderWithValidationGroupsEnabledCanReadFromMultipleValidationGroups(object $entity): void
    {
        $schema = $this->createObj(OA\Schema::class, []);
        $schema->merge([
            $this->createObj(OA\Property::class, ['property' => 'property1']),
        ]);
        $reader = $this->createConstraintReaderWithValidationGroupsEnabled();
        $reader->setSchema($schema);

        // no serialization groups passed here
        $reader->updateProperty(
            new \ReflectionProperty($entity, 'property1'),
            $schema->properties[0],
            ['other', Constraint::DEFAULT_GROUP]
        );

        self::assertSame(['property1'], $schema->required, 'should have read constraint in default group');
        self::assertSame(1, $schema->properties[0]->minimum, 'should have read constraint in other group');
    }

    public static function provideConstraintsWithGroups(): \Generator
    {
        if (interface_exists(Reader::class) && Kernel::MAJOR_VERSION < 7) {
            yield 'Annotations' => [new class() {
                /**
                 * @Assert\NotBlank()
                 *
                 * @Assert\Range(min=1, groups={"other"})
                 */
                public $property1;
            }];
        }

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\NotBlank()]
                #[Assert\Range(min: 1, groups: ['other'])]
                public $property1;
            }];
        }
    }

    private function createConstraintReaderWithValidationGroupsEnabled(): SymfonyConstraintAnnotationReader
    {
        return new SymfonyConstraintAnnotationReader(
            $this->doctrineAnnotations,
            true
        );
    }

    /**
     * @template T of OA\AbstractAnnotation
     *
     * @param class-string<T>      $className
     * @param array<string, mixed> $props
     *
     * @return T
     */
    private function createObj(string $className, array $props = []): object
    {
        return new $className($props + ['_context' => new Context()]);
    }
}
