<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber;

use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Tests\Functional\TestKernel;
use OpenApi\Annotations as OA;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\TypeInfo\Type;

final class ObjectModelDescriberTypeInfoTest extends ObjectModelDescriberTest
{
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel(TestKernel::USE_TYPE_INFO);
    }

    protected function setUp(): void
    {
        if (!version_compare(Kernel::VERSION, '7.2.0', '>=')) {
            self::markTestSkipped('TypeInfo component is only available in Symfony 7.2 and later');
        }

        parent::setUp();
    }

    #[\Override]
    public static function provideFixtures(): \Generator
    {
        /*
         * Checks if there is a replacement json file for the fixture
         * This can be done in cases where the TypeInfo components is able to provide a better schema
         */
        foreach (parent::provideFixtures() as $fixture) {
            $class = $fixture[0];

            $reflect = new \ReflectionClass($class);
            if (file_exists($fixtureDir = \dirname($reflect->getFileName()).'/TypeInfo/'.$reflect->getShortName().'.json')) {
                yield [
                    $class,
                    $fixtureDir,
                ];

                continue;
            }

            yield $fixture;
        }

        $finder = new Finder();
        $entityFiles = $finder->files()
            ->in(__DIR__.'/Fixtures/TypeInfo')
            ->name('*.php')
            ->sortByCaseInsensitiveName();

        foreach ($entityFiles as $file) {
            $namespacedPath = str_replace(__DIR__.'/Fixtures/TypeInfo', 'Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\Fixtures\TypeInfo', $file->getPathname());
            $pathWithBackslashes = str_replace('/', '\\', $namespacedPath);

            /** @var class-string $fullyQualifiedClassName */
            $fullyQualifiedClassName = str_replace('.php', '', $pathWithBackslashes);

            try {
                $classExists = class_exists($fullyQualifiedClassName);
            } catch (\Throwable) {
                // Skip classes that cannot be loaded (Unsupported syntax, etc.)
                continue;
            }

            if (!$classExists) {
                self::markTestIncomplete(\sprintf('The class "%s" does not exist.', $fullyQualifiedClassName));
            }

            yield [
                $fullyQualifiedClassName,
                str_replace('.php', '.json', $file->getPathname()),
            ];
        }
    }

    /**
     * @dataProvider provideInvalidTypes
     */
    public function testInvalidType(object $class, string $expectedType, string $propertyName): void
    {
        $model = new Model(Type::object($class::class));
        $schema = new OA\Schema([
            'type' => 'object',
        ]);

        self::expectException(\Exception::class);
        self::expectExceptionMessage(\sprintf('Type "%s" is not supported in %s::%s. You may need to use the `#[OA\Property(type="")]` attribute to specify it manually.', $expectedType, $class::class, $propertyName));

        $this->modelDescriber->describe($model, $schema);
    }

    public static function provideInvalidTypes(): \Generator
    {
        yield 'never' => [
            new class {
                public function getNever(): never
                {
                    throw new \Exception('This method should never be called');
                }
            },
            'never',
            '$never',
        ];

        yield 'void' => [
            new class {
                public function getVoid(): void
                {
                }
            },
            'void',
            '$void',
        ];
    }

    /**
     * `symfony/type-info 7.3.3` changed the string representation of list from `array<int, T>` to `list<T>`.
     *
     * This causes a change in the order of the types in a union type, which in turn changes the generated schema.
     */
    public function testComplexArray(): void
    {
        $complexArrayClass = new class {
            /**
             * @var list<int>|array<string, float>
             */
            public array $listOrDict;
        };

        $model = new Model(Type::object($complexArrayClass::class));
        $schema = new OA\Schema([
            'type' => 'object',
        ]);

        $this->modelDescriber->describe($model, $schema);
        $decodedSchema = json_decode($schema->toJson(), true, flags: \JSON_THROW_ON_ERROR);

        self::assertSame(['listOrDict'], $decodedSchema['required']);
        self::assertCount(1, $decodedSchema['properties']);
        self::assertArrayHasKey('listOrDict', $decodedSchema['properties']);
        $listOrDict = $decodedSchema['properties']['listOrDict'];
        self::assertIsArray($listOrDict);
        self::assertArrayHasKey('oneOf', $listOrDict);
        self::assertCount(2, $listOrDict['oneOf']);
        self::assertContains(
            [
                'type' => 'array',
                'items' => [
                    'type' => 'integer',
                ],
            ],
            $listOrDict['oneOf']
        );
        self::assertContains(
            [
                'type' => 'object',
                'additionalProperties' => [
                    'type' => 'number',
                    'format' => 'float',
                ],
            ],
            $listOrDict['oneOf']
        );
    }
}
