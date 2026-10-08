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

use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\ModelDescriber\Annotations\OpenApiAnnotationsReader;
use Nelmio\ApiDocBundle\Tests\ModelDescriber\Annotations\Fixture\PropertyWithExamples;
use Nelmio\ApiDocBundle\Tests\ModelDescriber\Annotations\Fixture\SchemaWithExamples;
use Nelmio\ApiDocBundle\Util\SetsContextTrait;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAattr;
use OpenApi\Context;
use OpenApi\Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class AnnotationReaderTest extends TestCase
{
    use SetsContextTrait;

    #[DataProvider('provideProperty')]
    public function testProperty(object $entity): void
    {
        $baseProps = ['_context' => new Context()];

        $schema = new OA\Schema($baseProps);
        $schema->merge([new OA\Property(['property' => 'property1'] + $baseProps)]);
        $schema->merge([new OA\Property(['property' => 'property2'] + $baseProps)]);

        $registry = new ModelRegistry([], new OA\OpenApi($baseProps), []);
        $symfonyConstraintAnnotationReader = new OpenApiAnnotationsReader(
            $registry,
            ['json']
        );
        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property1'), $schema->properties[0]);
        $symfonyConstraintAnnotationReader->updateProperty(new \ReflectionProperty($entity, 'property2'), $schema->properties[1]);

        self::assertEquals($schema->properties[0]->example, 1);
        self::assertEquals($schema->properties[0]->description, Generator::UNDEFINED);

        self::assertEquals($schema->properties[1]->example, 'some example');
        self::assertEquals($schema->properties[1]->description, 'some description');
    }

    public function testSchemaExamplesAreKeptWhenOpenApiVersionIs31(): void
    {
        $this->skipUnlessAttributesAcceptJsonSchemaExamples();

        $context = new Context(['version' => '3.1.0']);
        $schema = new OA\Schema(['_context' => $context]);
        $reader = new OpenApiAnnotationsReader(new ModelRegistry([], new OA\OpenApi(['_context' => $context]), []), ['json']);

        $reader->updateSchema(new \ReflectionClass(SchemaWithExamples::class), $schema);

        self::assertSame(['red', 'blue'], $schema->examples);
    }

    public function testPropertyExamplesAreKeptWhenOpenApiVersionIs31(): void
    {
        $this->skipUnlessAttributesAcceptJsonSchemaExamples();

        $context = new Context(['version' => '3.1.0']);
        $property = new OA\Property(['property' => 'label', '_context' => $context]);
        $reader = new OpenApiAnnotationsReader(new ModelRegistry([], new OA\OpenApi(['_context' => $context]), []), ['json']);

        $reader->updateProperty(new \ReflectionProperty(PropertyWithExamples::class, 'label'), $property);

        self::assertSame(['one', 'two'], $property->examples);
    }

    /**
     * Plain JSON Schema examples on attributes need zircote/swagger-php 6.8.1+.
     * Older releases only take Examples objects (Schema) or have no such argument at all (Property in 5.x).
     */
    private function skipUnlessAttributesAcceptJsonSchemaExamples(): void
    {
        // Older releases log the unexpected value while building the attribute; keep that out of the test output.
        $this->setContext(new Context(['logger' => new NullLogger()]));

        try {
            // Built through reflection so the call stays valid for static analysis on every supported swagger-php major.
            $property = (new \ReflectionClass(OAattr\Property::class))->newInstanceArgs(['examples' => ['probe']]);
        } catch (\Error) {
            $property = null;
        } finally {
            $this->setContext(null);
        }

        if (!$property instanceof OAattr\Property || ['probe'] !== $property->examples) {
            self::markTestSkipped('The installed zircote/swagger-php attributes do not accept JSON Schema examples.');
        }
    }

    public static function provideProperty(): \Generator
    {
        yield 'Attributes' => [new class {
            #[OAattr\Property(example: 1)]
            public $property1;
            #[OAattr\Property(example: 'some example', description: 'some description')]
            public $property2;
        }];
    }
}
