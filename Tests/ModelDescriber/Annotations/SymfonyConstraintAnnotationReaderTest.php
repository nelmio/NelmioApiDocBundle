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
}
