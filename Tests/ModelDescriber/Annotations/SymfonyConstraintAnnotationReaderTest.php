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

    public function testAssertChoiceResultsInNumericArray()
    {
        define('TEST_ASSERT_CHOICE_STATUSES', [
            1 => 'active',
            2 => 'blocked',
        ]);

        $entity = new class() {
            /**
             * @Assert\Length(min = 1)
             * @Assert\Choice(choices=TEST_ASSERT_CHOICE_STATUSES)
             */
            private $property1;
        };

        $schema = new OA\Schema([]);
        $schema->merge([new OA\Property(['property' => 'property1'])]);

        $symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader(new AnnotationReader());
        $symfonyConstraintAnnotationReader->setSchema($schema);

        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);

        // expect enum to be numeric array with sequential keys (not [1 => "active", 2 => "active"])
        $this->assertEquals($schema->properties[0]->enum, ['active', 'blocked']);
    }
}
