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
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PropertyInfo\Type as LegacyType;

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

    public static function provideFixtures(): \Generator
    {
        /*
         * Checks if there is a replacement json file for the fixture
         * This can be done in cases where the TypeInfo components is able to provide a better schema
         */
        foreach (parent::provideFixtures() as $fixture) {
            $class = $fixture[0];

            $reflect = new \ReflectionClass($class);
            if (file_exists($fixtureDir = dirname($reflect->getFileName()).'/TypeInfo/'.$reflect->getShortName().'.json')) {
                yield [
                    $class,
                    $fixtureDir
                ];

                continue;
            }

            yield $fixture;
        }

        yield [
            Fixtures\TypeInfo\ArrayMixedKeys::class
        ];

        yield [
            Fixtures\TypeInfo\MixedTypes::class
        ];

        yield [
            Fixtures\TypeInfo\ClassWithIntersectionNullable::class
        ];

        yield [
            Fixtures\TypeInfo\UuidClass::class
        ];

        yield [
            Fixtures\TypeInfo\DateTimeClass::class
        ];
    }

    /**
     * @dataProvider provideInvalidTypes
     */
    public function testInvalidType(object $class, string $expectedType, string $propertyName): void
    {
        $model = new Model(new LegacyType('object', false, get_class($class)));
        $schema = new OA\Schema([
            'type' => 'object',
        ]);

        self::expectException(\Exception::class);
        self::expectExceptionMessage(sprintf('Type "%s" not supported in %s::%s. You may need to use the `@OA\Property(type="")` annotation to specify it manually.', $expectedType, get_class($class), $propertyName));

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
}
