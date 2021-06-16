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
use Nelmio\ApiDocBundle\ModelDescriber\Annotations\SymfonyConstraintAnnotationReader;
use Nelmio\ApiDocBundle\Tests\Helper;
use Nelmio\ApiDocBundle\Tests\ModelDescriber\Annotations\Fixture as CustomAssert;
use OpenApi\Annotations as OA;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints as Assert;

class SymfonyConstraintAnnotationReaderTest extends TestCase
{
    public function testUpdatePropertyFix1283()
    {
        $entity = new class() {
            /**
             * @Assert\NotBlank()
             * @Assert\Length(min = 1)
             */
            private $property1;
            /**
             * @Assert\NotBlank()
             */
            private $property2;
        };

        $schema = new OA\Schema([]);
        $schema->merge([new OA\Property(['property' => 'property1'])]);
        $schema->merge([new OA\Property(['property' => 'property2'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader(new AnnotationReader());
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);
        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property2'), $schema->properties[1]);

        // expect required to be numeric array with sequential keys (not [0 => ..., 2 => ...])
        $this->assertEquals($schema->required, ['property1', 'property2']);
    }

    /**
     * @param object $entity
     * @dataProvider provideOptionalProperty
     */
    public function testOptionalProperty($entity)
    {
        if (!\property_exists(Assert\NotBlank::class, 'allowNull')) {
            $this->markTestSkipped('NotBlank::allowNull was added in symfony/validator 4.3.');
        }

        $schema = new OA\Schema([]);
        $schema->merge([new OA\Property(['property' => 'property1'])]);
        $schema->merge([new OA\Property(['property' => 'property2'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader(new AnnotationReader());
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);
        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property2'), $schema->properties[1]);

        // expect required to be numeric array with sequential keys (not [0 => ..., 2 => ...])
        $this->assertEquals($schema->required, ['property2']);
    }

    public function provideOptionalProperty(): iterable
    {
        yield 'Annotations' => [new class() {
            /**
             * @Assert\NotBlank(allowNull = true)
             * @Assert\Length(min = 1)
             */
            private $property1;
            /**
             * @Assert\NotBlank()
             */
            private $property2;
        }];

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\NotBlank(allowNull: true)]
                #[Assert\Length(min: 1)]
                private $property1;
                #[Assert\NotBlank]
                private $property2;
            }];
        }
    }

    /**
     * @param object $entity
     * @dataProvider provideAssertChoiceResultsInNumericArray
     */
    public function testAssertChoiceResultsInNumericArray($entity)
    {
        $schema = new OA\Schema([]);
        $schema->merge([new OA\Property(['property' => 'property1'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader(new AnnotationReader());
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);

        // expect enum to be numeric array with sequential keys (not [1 => "active", 2 => "active"])
        $this->assertEquals($schema->properties[0]->enum, ['active', 'blocked']);
    }

    public function provideAssertChoiceResultsInNumericArray(): iterable
    {
        define('TEST_ASSERT_CHOICE_STATUSES', [
            1 => 'active',
            2 => 'blocked',
        ]);

        yield 'Annotations' => [new class() {
            /**
             * @Assert\Length(min = 1)
             * @Assert\Choice(choices=TEST_ASSERT_CHOICE_STATUSES)
             */
            private $property1;
        }];

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\Length(min: 1)]
                #[Assert\Choice(choices: TEST_ASSERT_CHOICE_STATUSES)]
                private $property1;
            }];
        }
    }

    /**
     * @param object $entity
     * @dataProvider provideMultipleChoiceConstraintsApplyEnumToItems
     */
    public function testMultipleChoiceConstraintsApplyEnumToItems($entity)
    {
        $schema = new OA\Schema([]);
        $schema->merge([new OA\Property(['property' => 'property1'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader(new AnnotationReader());
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);

        $this->assertInstanceOf(OA\Items::class, $schema->properties[0]->items);
        $this->assertEquals($schema->properties[0]->items->enum, ['one', 'two']);
    }

    public function provideMultipleChoiceConstraintsApplyEnumToItems(): iterable
    {
        yield 'Annotations' => [new class() {
            /**
             * @Assert\Choice(choices={"one", "two"}, multiple=true)
             */
            private $property1;
        }];

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\Choice(choices: ['one', 'two'], multiple: true)]
                private $property1;
            }];
        }
    }

    /**
     * @param object $entity
     * @group https://github.com/nelmio/NelmioApiDocBundle/issues/1780
     * @dataProvider provideLengthConstraintDoesNotSetMaxLengthIfMaxIsNotSet
     */
    public function testLengthConstraintDoesNotSetMaxLengthIfMaxIsNotSet($entity)
    {
        $schema = new OA\Schema([]);
        $schema->merge([new OA\Property(['property' => 'property1'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader(new AnnotationReader());
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);

        $this->assertSame(OA\UNDEFINED, $schema->properties[0]->maxLength);
        $this->assertSame(1, $schema->properties[0]->minLength);
    }

    public function provideLengthConstraintDoesNotSetMaxLengthIfMaxIsNotSet(): iterable
    {
        yield 'Annotations' => [new class() {
            /**
             * @Assert\Length(min = 1)
             */
            private $property1;
        }];

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\Length(min: 1)]
                private $property1;
            }];
        }
    }

    /**
     * @param object $entity
     * @group https://github.com/nelmio/NelmioApiDocBundle/issues/1780
     * @dataProvider provideLengthConstraintDoesNotSetMinLengthIfMinIsNotSet
     */
    public function testLengthConstraintDoesNotSetMinLengthIfMinIsNotSet($entity)
    {
        $schema = new OA\Schema([]);
        $schema->merge([new OA\Property(['property' => 'property1'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader(new AnnotationReader());
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);

        $this->assertSame(OA\UNDEFINED, $schema->properties[0]->minLength);
        $this->assertSame(100, $schema->properties[0]->maxLength);
    }

    public function provideLengthConstraintDoesNotSetMinLengthIfMinIsNotSet(): iterable
    {
        yield 'Annotations' => [new class() {
            /**
             * @Assert\Length(max = 100)
             */
            private $property1;
        }];

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\Length(max: 100)]
                private $property1;
            }];
        }
    }

    public function testCompoundValidationRules()
    {
        $entity = new class() {
            /**
             * @CustomAssert\CompoundValidationRule()
             */
            private $property1;
        };
        $propertyName = 'property1';

        $schema = new OA\Schema([]);
        $schema->merge([new OA\Property(['property' => $propertyName])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader(new AnnotationReader());
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, $propertyName), $schema->properties[0]);

        if (Helper::isCompoundValidatorConstraintSupported()) {
            $this->assertSame([$propertyName], $schema->required);
            $this->assertSame(0, $schema->properties[0]->minimum);
            $this->assertTrue($schema->properties[0]->exclusiveMinimum);
            $this->assertSame(5, $schema->properties[0]->maximum);
            $this->assertTrue($schema->properties[0]->exclusiveMaximum);
        } else {
            $this->assertSame(OA\UNDEFINED, $schema->required);
            $this->assertSame(OA\UNDEFINED, $schema->properties[0]->minimum);
            $this->assertSame(OA\UNDEFINED, $schema->properties[0]->exclusiveMinimum);
            $this->assertSame(OA\UNDEFINED, $schema->properties[0]->maximum);
            $this->assertSame(OA\UNDEFINED, $schema->properties[0]->exclusiveMaximum);
        }
    }

    /**
     * @param object $entity
     * @group https://github.com/nelmio/NelmioApiDocBundle/issues/1821
     * @dataProvider provideCountConstraintDoesNotSetMinItemsIfMinIsNotSet
     */
    public function testCountConstraintDoesNotSetMinItemsIfMinIsNotSet($entity)
    {
        $schema = new OA\Schema([]);
        $schema->merge([new OA\Property(['property' => 'property1'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader(new AnnotationReader());
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);

        $this->assertSame(OA\UNDEFINED, $schema->properties[0]->minItems);
        $this->assertSame(10, $schema->properties[0]->maxItems);
    }

    public function provideCountConstraintDoesNotSetMinItemsIfMinIsNotSet(): iterable
    {
        yield 'Annotations' => [new class() {
            /**
             * @Assert\Count(max = 10)
             */
            private $property1;
        }];

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\Count(max: 10)]
                private $property1;
            }];
        }
    }

    /**
     * @param object $entity
     * @group https://github.com/nelmio/NelmioApiDocBundle/issues/1821
     * @dataProvider provideCountConstraintDoesNotSetMaxItemsIfMaxIsNotSet
     */
    public function testCountConstraintDoesNotSetMaxItemsIfMaxIsNotSet($entity)
    {
        $schema = new OA\Schema([]);
        $schema->merge([new OA\Property(['property' => 'property1'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader(new AnnotationReader());
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);

        $this->assertSame(OA\UNDEFINED, $schema->properties[0]->maxItems);
        $this->assertSame(10, $schema->properties[0]->minItems);
    }

    public function provideCountConstraintDoesNotSetMaxItemsIfMaxIsNotSet(): iterable
    {
        yield 'Annotations' => [new class() {
            /**
             * @Assert\Count(min = 10)
             */
            private $property1;
        }];

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\Count(min: 10)]
                private $property1;
            }];
        }
    }

    /**
     * @param object $entity
     * @group https://github.com/nelmio/NelmioApiDocBundle/issues/1822
     * @dataProvider provideRangeConstraintDoesNotSetMaximumIfMaxIsNotSet
     */
    public function testRangeConstraintDoesNotSetMaximumIfMaxIsNotSet($entity)
    {
        $schema = new OA\Schema([]);
        $schema->merge([new OA\Property(['property' => 'property1'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader(new AnnotationReader());
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);

        $this->assertSame(OA\UNDEFINED, $schema->properties[0]->maximum);
        $this->assertSame(10, $schema->properties[0]->minimum);
    }

    public function provideRangeConstraintDoesNotSetMaximumIfMaxIsNotSet(): iterable
    {
        yield 'Annotations' => [new class() {
            /**
             * @Assert\Range(min = 10)
             */
            private $property1;
        }];

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\Range(min: 10)]
                private $property1;
            }];
        }
    }

    /**
     * @param object $entity
     * @group https://github.com/nelmio/NelmioApiDocBundle/issues/1822
     * @dataProvider provideRangeConstraintDoesNotSetMinimumIfMinIsNotSet
     */
    public function testRangeConstraintDoesNotSetMinimumIfMinIsNotSet($entity)
    {
        $schema = new OA\Schema([]);
        $schema->merge([new OA\Property(['property' => 'property1'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader(new AnnotationReader());
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);

        $this->assertSame(OA\UNDEFINED, $schema->properties[0]->minimum);
        $this->assertSame(10, $schema->properties[0]->maximum);
    }

    public function provideRangeConstraintDoesNotSetMinimumIfMinIsNotSet(): iterable
    {
        yield 'Annotations' => [new class() {
            /**
             * @Assert\Range(max = 10)
             */
            private $property1;
        }];

        if (\PHP_VERSION_ID >= 80000) {
            yield 'Attributes' => [new class() {
                #[Assert\Range(max: 10)]
                private $property1;
            }];
        }
    }
}
