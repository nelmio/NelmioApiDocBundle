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

class ObjectModelDescriberTestTypeInfo extends ObjectModelDescriberTest
{
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel(TestKernel::USE_TYPE_INFO);
    }

    /**
     * @dataProvider provideFixtures
     * //     * @dataProvider provideTypeInfoFixtures
     */
    public function testItDescribes(string $class): void
    {
        parent::testItDescribes($class);
    }

    public static function provideTypeInfoFixtures(): \Generator
    {
        yield [
            Fixtures\TypeInfo\ArrayMixedKeys::class
        ];
    }
}
