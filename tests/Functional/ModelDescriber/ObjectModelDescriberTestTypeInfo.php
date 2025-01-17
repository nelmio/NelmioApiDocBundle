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

use Nelmio\ApiDocBundle\Tests\Functional\TestKernel;
use Symfony\Component\HttpKernel\KernelInterface;

final class ObjectModelDescriberTestTypeInfo extends ObjectModelDescriberTest
{
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel(TestKernel::USE_TYPE_INFO);
    }

    /**
     * @dataProvider provideFixtures
     */
    public function testItDescribes(string $class, ?string $fixtureDir = null): void
    {
        parent::testItDescribes($class, $fixtureDir);
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
    }
}
