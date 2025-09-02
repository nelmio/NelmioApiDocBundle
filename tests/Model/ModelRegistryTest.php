<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Model;

use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyInfo\Type;

class ModelRegistryTest extends TestCase
{
    public function testNameAliasingNotAppliedForCollections(): void
    {
        $alternativeNames = [
            'Foo1' => [
                'type' => self::class,
                'groups' => ['group1'],
            ],
        ];
        $registry = new ModelRegistry([], $this->createOpenApi(), $alternativeNames);
        $type = new Type(Type::BUILTIN_TYPE_ARRAY, false, null, true);

        self::assertEquals('#/components/schemas/array', $registry->register(new Model($type, ['group1'])));
    }

    /**
     * @param array<string, mixed> $arrayType
     */
    #[DataProvider('provideNameCollisionsTypes')]
    public function testNameCollisionsAreLogged(Type $type, array $arrayType): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('info')
            ->with(
                'Can not assign a name for the model, the name "ModelRegistryTest" has already been taken.',
                [
                    'model' => [
                        'type' => $arrayType,
                        'options' => [],
                        'groups' => ['group2'],
                        'serialization_context' => [
                            'groups' => ['group2'],
                        ],
                    ],
                    'taken_by' => [
                        'type' => $arrayType,
                        'options' => [],
                        'groups' => ['group1'],
                        'serialization_context' => [
                            'groups' => ['group1'],
                            'extra_context' => true,
                        ],
                    ],
                ]
            );

        $registry = new ModelRegistry([], $this->createOpenApi(), []);
        $registry->setLogger($logger);

        $registry->register(new Model($type, ['group1'], [], ['extra_context' => true]));
        $registry->register(new Model($type, ['group2']));
    }

    public static function provideNameCollisionsTypes(): \Generator
    {
        yield [
            new Type(Type::BUILTIN_TYPE_OBJECT, false, self::class),
            [
                'class' => 'Nelmio\\ApiDocBundle\\Tests\\Model\\ModelRegistryTest',
                'built_in_type' => 'object',
                'nullable' => false,
                'collection' => false,
                'collection_key_types' => null,
                'collection_value_types' => null,
            ],
        ];

        yield [
            new Type(Type::BUILTIN_TYPE_OBJECT, false, self::class, true, new Type(Type::BUILTIN_TYPE_OBJECT)),
            [
                'class' => 'Nelmio\\ApiDocBundle\\Tests\\Model\\ModelRegistryTest',
                'built_in_type' => 'object',
                'nullable' => false,
                'collection' => true,
                'collection_key_types' => [
                    [
                        'class' => null,
                        'built_in_type' => 'object',
                        'nullable' => false,
                        'collection' => false,
                        'collection_key_types' => null,
                        'collection_value_types' => null,
                    ],
                ],
                'collection_value_types' => [],
            ],
        ];
    }

    public function testNameCollisionsAreLoggedWithAlternativeNames(): void
    {
        $ref = new \ReflectionClass(self::class);
        $alternativeNames = [
            $ref->getShortName() => [
                'type' => $ref->getName(),
                'groups' => ['group1'],
            ],
        ];
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('info')
            ->with(
                'Can not assign a name for the model, the name "ModelRegistryTest" has already been taken.',
                [
                    'model' => [
                        'type' => [
                            'class' => 'Nelmio\\ApiDocBundle\\Tests\\Model\\ModelRegistryTest',
                            'built_in_type' => 'object',
                            'nullable' => false,
                            'collection' => false,
                            'collection_key_types' => null,
                            'collection_value_types' => null,
                        ],
                        'options' => [],
                        'groups' => ['group2'],
                        'serialization_context' => ['groups' => ['group2']],
                    ],
                    'taken_by' => [
                        'type' => [
                            'class' => 'Nelmio\\ApiDocBundle\\Tests\\Model\\ModelRegistryTest',
                            'built_in_type' => 'object',
                            'nullable' => false,
                            'collection' => false,
                            'collection_key_types' => null,
                            'collection_value_types' => null,
                        ],
                        'options' => [],
                        'groups' => ['group1'],
                        'serialization_context' => ['groups' => ['group1']],
                    ],
                ]
            );

        $registry = new ModelRegistry([], $this->createOpenApi(), $alternativeNames);
        $registry->setLogger($logger);

        $type = new Type(Type::BUILTIN_TYPE_OBJECT, false, self::class);
        $registry->register(new Model($type, ['group2']));
    }

    /**
     * @param string[]|null        $groups
     * @param array<string, mixed> $alternativeNames
     */
    #[DataProvider('getNameAlternatives')]
    public function testNameAliasingForObjects(string $expected, ?array $groups, ?string $name, array $alternativeNames): void
    {
        $registry = new ModelRegistry([], $this->createOpenApi(), $alternativeNames);
        $type = new Type(Type::BUILTIN_TYPE_OBJECT, false, self::class);

        self::assertEquals($expected, $registry->register(new Model($type, $groups, name: $name)));
    }

    public static function getNameAlternatives(): \Generator
    {
        yield [
            '#/components/schemas/ModelRegistryTest',
            null,
            null,
            [
                'Foo1' => [
                    'type' => self::class,
                    'groups' => ['group1'],
                ],
            ],
        ];

        yield [
            '#/components/schemas/Foo1',
            ['group1'],
            null,
            [
                'Foo1' => [
                    'type' => self::class,
                    'groups' => ['group1'],
                ],
            ],
        ];

        yield [
            '#/components/schemas/FooManualNaming',
            null,
            'FooManualNaming',
            [
                'Foo1' => [
                    'type' => self::class,
                    'groups' => ['group1'],
                ],
            ],
        ];

        yield [
            '#/components/schemas/FooManualNaming',
            ['group1'],
            'FooManualNaming',
            [
                'Foo1' => [
                    'type' => self::class,
                    'groups' => ['group1'],
                ],
            ],
        ];

        yield [
            '#/components/schemas/Foo1',
            ['group1', 'group2'],
            null,
            [
                'Foo1' => [
                    'type' => self::class,
                    'groups' => ['group1', 'group2'],
                ],
            ],
        ];

        yield [
            '#/components/schemas/ModelRegistryTest',
            null,
            null,
            [
                'Foo1' => [
                    'type' => self::class,
                    'groups' => [],
                ],
            ],
        ];

        yield [
            '#/components/schemas/Foo1',
            [],
            null,
            [
                'Foo1' => [
                    'type' => self::class,
                    'groups' => [],
                ],
            ],
        ];
    }

    public function testMultipleSchemasSameCustomName(): void
    {
        $registry = new ModelRegistry([], $this->createOpenApi());
        $name = 'CustomName';

        self::assertEquals('#/components/schemas/CustomName', $registry->register(new Model(new Type(Type::BUILTIN_TYPE_OBJECT, false, self::class), name: $name)));
        self::assertEquals('#/components/schemas/CustomName2', $registry->register(new Model(new Type(Type::BUILTIN_TYPE_OBJECT, false, self::class.'Foo'), name: $name)));
        self::assertEquals('#/components/schemas/CustomName3', $registry->register(new Model(new Type(Type::BUILTIN_TYPE_OBJECT, false, self::class.'Bar'), name: $name)));
    }

    // Re-using the same custom name with an identical model should return the same schema reference
    public function testMultipleSchemasSameCustomNameReusesReference(): void
    {
        $registry = new ModelRegistry([], $this->createOpenApi());
        $name = 'CustomName';

        $type = new Type(Type::BUILTIN_TYPE_OBJECT, false, self::class.'ReUsed');

        self::assertEquals('#/components/schemas/CustomName', $registry->register(new Model($type, name: $name)));
        self::assertEquals('#/components/schemas/CustomName', $registry->register(new Model($type, name: $name)));
        self::assertEquals('#/components/schemas/CustomName', $registry->register(new Model($type, name: $name)));
        self::assertEquals('#/components/schemas/ModelRegistryTestReUsed', $registry->register(new Model($type)));
    }

    #[DataProvider('unsupportedTypesProvider')]
    public function testUnsupportedTypeException(Type $type, string $stringType): void
    {
        $this->expectException('\LogicException');
        $this->expectExceptionMessage(\sprintf('Schema of type "%s" can\'t be generated, no describer supports it.', $stringType));

        $registry = new ModelRegistry([], $this->createOpenApi());
        $registry->register(new Model($type));
        $registry->registerSchemas();
    }

    public static function unsupportedTypesProvider(): \Generator
    {
        yield [new Type(Type::BUILTIN_TYPE_ARRAY, false, null, true), 'mixed[]'];
        yield [new Type(Type::BUILTIN_TYPE_OBJECT, false, self::class), '\\'.self::class];
    }

    public function testUnsupportedTypeExceptionWithNonExistentClass(): void
    {
        $className = 'Some\\Class\\That\\DoesNotExist';
        $type = new Type(Type::BUILTIN_TYPE_OBJECT, false, $className);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Schema of type "\%s" can\'t be generated, no describer supports it. Class "\Some\Class\That\DoesNotExist" does not exist, did you forget a use statement, or typed it wrong?', $className));

        $registry = new ModelRegistry([], $this->createOpenApi());
        $registry->register(new Model($type));
        $registry->registerSchemas();
    }

    private function createOpenApi(): OA\OpenApi
    {
        return new OA\OpenApi(['_context' => new Context()]);
    }
}
