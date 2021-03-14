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
use EXSyst\Component\Swagger\Schema;
use Nelmio\ApiDocBundle\ModelDescriber\Annotations\SymfonyConstraintAnnotationReader;
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

        $schema = new Schema();
        $schema->getProperties()->set('property1', new Schema());
        $schema->getProperties()->set('property2', new Schema());

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader(new AnnotationReader());
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->getProperties()->get('property1'));
        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property2'), $schema->getProperties()->get('property2'));

        // expect required to be numeric array with sequential keys (not [0 => ..., 2 => ...])
        $this->assertEquals($schema->getRequired(), ['property1', 'property2']);
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

        $schema = new Schema();
        $schema->getProperties()->set('property1', new Schema());
        $schema->getProperties()->set('property2', new Schema());

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader(new AnnotationReader());
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->getProperties()->get('property1'));
        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property2'), $schema->getProperties()->get('property2'));

        // expect required to be numeric array with sequential keys (not [0 => ..., 2 => ...])
        $this->assertEquals($schema->getRequired(), ['property2']);
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
        $schema = new Schema();
        $schema->getProperties()->set('property1', new Schema());

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader(new AnnotationReader());
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->getProperties()->get('property1'));

        // expect enum to be numeric array with sequential keys (not [1 => "active", 2 => "active"])
        $this->assertEquals($schema->getProperties()->get('property1')->getEnum(), ['active', 'blocked']);
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
}
