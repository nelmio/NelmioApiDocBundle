<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests;

use Nelmio\ApiDocBundle\ApiDocGenerator;
use Nelmio\ApiDocBundle\Describer\DefaultDescriber;
use Nelmio\ApiDocBundle\Describer\DescriberInterface;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class ApiDocGeneratorTest extends TestCase
{
    public function testCache(): void
    {
        $adapter = new ArrayAdapter();
        $generator = new ApiDocGenerator([new DefaultDescriber()], [], $adapter, null, new Generator());

        self::assertEquals(json_encode($generator->generate()), json_encode($adapter->getItem('openapi_doc')->get()));
    }

    public function testCacheWithCustomId(): void
    {
        $adapter = new ArrayAdapter();
        $generator = new ApiDocGenerator([new DefaultDescriber()], [], $adapter, 'custom_id', new Generator());

        self::assertEquals(json_encode($generator->generate()), json_encode($adapter->getItem('custom_id')->get()));
    }

    public function testResetClearsInMemoryDocument(): void
    {
        $generator = new ApiDocGenerator([new DefaultDescriber()], [], null, null, new Generator());

        $first = $generator->generate();
        self::assertSame($first, $generator->generate());

        $generator->reset();

        $second = $generator->generate();
        self::assertNotSame($first, $second);
        self::assertEquals(json_encode($first), json_encode($second));
    }

    public function testFailedGenerationDoesNotPoisonInMemoryCache(): void
    {
        $describer = new class implements DescriberInterface {
            public bool $fail = true;

            public function describe(OpenApi $api): void
            {
                if ($this->fail) {
                    throw new \RuntimeException('boom');
                }
            }
        };

        $generator = new ApiDocGenerator([$describer, new DefaultDescriber()], [], null, null, new Generator());

        try {
            $generator->generate();
            self::fail('Expected RuntimeException');
        } catch (\RuntimeException $e) {
            self::assertSame('boom', $e->getMessage());
        }

        $describer->fail = false;

        $beforeReset = $generator->generate();
        $generator->reset();
        $afterReset = $generator->generate();

        self::assertNotSame($beforeReset, $afterReset);
        self::assertEquals(json_encode($beforeReset), json_encode($afterReset));
    }
}
