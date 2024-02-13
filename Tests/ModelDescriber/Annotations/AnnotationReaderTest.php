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
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\ModelDescriber\Annotations\OpenApiAnnotationsReader;
use Nelmio\ApiDocBundle\Util\SetsContextTrait;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAattr;
use OpenApi\Context;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use const PHP_VERSION_ID;

class AnnotationReaderTest extends TestCase
{
    use SetsContextTrait;

    /**
     * @param object $entity
     *
     * @dataProvider provideProperty
     */
    public function testProperty($entity)
    {
        $baseProps = ['_context' => new Context()];

        $schema = new OA\Schema($baseProps);
        $schema->merge([new OA\Property(['property' => 'property1'] + $baseProps)]);
        $schema->merge([new OA\Property(['property' => 'property2'] + $baseProps)]);

        $registry = new ModelRegistry([], new OA\OpenApi($baseProps), []);
        $symfonyConstraintAnnotationReader = new OpenApiAnnotationsReader(
            class_exists(AnnotationReader::class) ? new AnnotationReader() : null,
            $registry,
            ['json']
        );
        $symfonyConstraintAnnotationReader->updateProperty(new ReflectionProperty($entity, 'property1'), $schema->properties[0]);
        $symfonyConstraintAnnotationReader->updateProperty(new ReflectionProperty($entity, 'property2'), $schema->properties[1]);

        $this->assertEquals($schema->properties[0]->example, 1);
        $this->assertEquals($schema->properties[0]->description, Generator::UNDEFINED);

        $this->assertEquals($schema->properties[1]->example, 'some example');
        $this->assertEquals($schema->properties[1]->description, 'some description');
    }

    public function provideProperty(): iterable
    {
        yield 'Annotations' => [new class() {
            /**
             * @OA\Property(example=1)
             */
            private $property1;
            /**
             * @OA\Property(example="some example", description="some description")
             */
            private $property2;
        }];

        if (PHP_VERSION_ID >= 80100) {
            yield 'Attributes' => [new class() {
                #[OAattr\Property(example: 1)]
                private $property1;
                #[OAattr\Property(example: 'some example', description: 'some description')]
                private $property2;
            }];
        }
    }
}
