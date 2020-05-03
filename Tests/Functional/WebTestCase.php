<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional;

use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{
    protected static function createKernel(array $options = [])
    {
        return new TestKernel();
    }

    protected function getOpenApiDefinition($area = 'default'): OA\OpenApi
    {
        return static::$kernel->getContainer()->get(sprintf('nelmio_api_doc.generator.%s', $area))->generate();
    }

    protected function getModel($name): OA\Schema
    {
        $api = $this->getOpenApiDefinition();
        $key = array_search($name, array_column($api->components->schemas, 'schema'), true);
        static::assertNotFalse($key, sprintf('Model "%s" does not exist.', $name));

        return $api->components->schemas[$key];
    }

    protected function getOperation($path, $method): OA\Operation
    {
        $path = $this->getPath($path);

        $this->assertInstanceOf(
            OA\Operation::class,
            $path->{$method},
            sprintf('Operation "%s" for path "%s" does not exist', $method, $path->path)
        );

        return $path->{$method};
    }

    protected function getPath($path): OA\PathItem
    {
        $api = $this->getOpenApiDefinition();
        $paths = array_column($api->paths !== OA\UNDEFINED ? $api->paths : [], 'path');
        static::assertContains(
            $path,
            $paths,
            sprintf('Failed asserting that path "%s" does exist.', $path)
        );

        return $api->paths[array_search($path, array_column($api->paths, 'path'), true)];
    }
}
