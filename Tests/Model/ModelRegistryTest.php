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
use PHPUnit\Framework\TestCase;
use Swagger\Annotations\Swagger;
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
        $registry = new ModelRegistry([], new Swagger([]), $alternativeNames);
        $type = new Type(Type::BUILTIN_TYPE_ARRAY, false, null, true);

        $this->assertEquals('#/definitions/array', $registry->register(new Model($type, ['group1'])));
    }

    /**
     * @dataProvider getNameAlternatives
     *
     * @param string $expected
     * @param        $groups
     * @param array  $alternativeNames
     */
    public function testNameAliasingForObjects(string $expected, $groups, array $alternativeNames): void
    {
        $registry = new ModelRegistry([], new Swagger([]), $alternativeNames);
        $type = new Type(Type::BUILTIN_TYPE_OBJECT, false, self::class);

        $this->assertEquals($expected, $registry->register(new Model($type, $groups)));
    }

    public function getNameAlternatives(): array
    {
        return [
            [
                '#/definitions/ModelRegistryTest',
                null,
                [
                    'Foo1' => [
                        'type' => self::class,
                        'groups' => ['group1'],
                    ],
                ],
            ],
            [
                '#/definitions/Foo1',
                ['group1'],
                [
                    'Foo1' => [
                        'type' => self::class,
                        'groups' => ['group1'],
                    ],
                ],
            ],
            [
                '#/definitions/Foo1',
                ['group1', 'group2'],
                [
                    'Foo1' => [
                        'type' => self::class,
                        'groups' => ['group1', 'group2'],
                    ],
                ],
            ],
            [
                '#/definitions/ModelRegistryTest',
                null,
                [
                    'Foo1' => [
                        'type' => self::class,
                        'groups' => [],
                    ],
                ],
            ],
            [
                '#/definitions/Foo1',
                [],
                [
                    'Foo1' => [
                        'type' => self::class,
                        'groups' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider unsupportedTypesProvider
     *
     * @param Type   $type
     * @param string $stringType
     */
    public function testUnsupportedTypeException(Type $type, string $stringType): void
    {
        $this->expectException('\LogicException');
        $this->expectExceptionMessage(sprintf('Definition of type "%s" can\'t be generated, no describer supports it.', $stringType));

        $registry = new ModelRegistry([], new Swagger([]));
        $registry->register(new Model($type));
        $registry->registerDefinitions();
    }

    public function unsupportedTypesProvider(): array
    {
        return [
            [new Type(Type::BUILTIN_TYPE_ARRAY, false, null, true), 'mixed[]'],
            [new Type(Type::BUILTIN_TYPE_OBJECT, false, self::class), self::class],
        ];
    }
}
