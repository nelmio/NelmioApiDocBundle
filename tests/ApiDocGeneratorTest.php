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

    public function testSaveToFile(): void
    {
        $adapter = new ArrayAdapter();
        $generator = new ApiDocGenerator([new DefaultDescriber()], [], $adapter, null, new Generator());

        $filePath = 'openapi_doc_test.json';
        $generator->saveToFile($generator->generate(), $filePath);

        self::assertFileExists($filePath);
        self::assertJsonStringEqualsJsonFile($filePath, json_encode($generator->generate()));

        unlink($filePath);
    }

    public function testLoadFromFile(): void
    {
        $adapter = new ArrayAdapter();
        $generator = new ApiDocGenerator([new DefaultDescriber()], [], $adapter, null, new Generator());

        $filePath = 'openapi_doc_test.json';
        file_put_contents($filePath, json_encode($generator->generate()));

        $loadedOpenApi = $generator->loadFromFile($filePath);

        self::assertEquals(json_encode($generator->generate()), json_encode($loadedOpenApi));

        unlink($filePath);
    }

    public function testGenerate(): void
    {
        $adapter = new ArrayAdapter();
        $generator = new ApiDocGenerator([new DefaultDescriber()], [], $adapter, null, new Generator());

        $filePath = 'openapi_doc_test.json';
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $openApi = $generator->generate();
        self::assertEquals(json_encode($openApi), json_encode($generator->generate()));

        self::assertFileExists($filePath);
        self::assertJsonStringEqualsJsonFile($filePath, json_encode($openApi));

        unlink($filePath);
    }
}
