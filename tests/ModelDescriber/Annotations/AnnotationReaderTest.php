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

use Composer\InstalledVersions;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\ModelDescriber\Annotations\OpenApiAnnotationsReader;
use Nelmio\ApiDocBundle\Util\SetsContextTrait;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAattr;
use OpenApi\Context;
use OpenApi\Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

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

    public function testSchemaExamplesWithOpenApi31(): void
    {
        if (version_compare(InstalledVersions::getVersion('zircote/swagger-php') ?? '0', '6.8.1', '<')) {
            self::markTestSkipped('JSON Schema examples on attributes require zircote/swagger-php 6.8.1 or later.');
        }

        $context = new Context(['version' => '3.1.0']);
        $schema = new OA\Schema(['_context' => $context]);

        $registry = new ModelRegistry([], new OA\OpenApi(['_context' => $context]), []);
        $openApiAnnotationsReader = new OpenApiAnnotationsReader($registry, ['json']);
        $openApiAnnotationsReader->updateSchema(new \ReflectionClass(new #[OAattr\Schema(examples: ['red', 'blue'])] class {}), $schema);

        self::assertSame(['red', 'blue'], $schema->examples);
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
