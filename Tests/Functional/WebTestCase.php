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

use Swagger\Annotations\AbstractAnnotation;
use Swagger\Annotations\Definition;
use Swagger\Annotations\Items;
use Swagger\Annotations\Operation;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Path;
use Swagger\Annotations\Property;
use Swagger\Annotations\Response;
use Swagger\Annotations\Schema;
use Swagger\Annotations\Swagger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{
    protected static $swaggerDefinition;

    public function assertHasPath(string $path, Swagger $api): void
    {
        $paths = array_column($api->paths ?: [], 'path');

        static::assertContains(
            $path,
            $paths,
            sprintf('Failed asserting that path "%s" does exist.', $path)
        );
    }

    public function assertNotHasPath(string $path, Swagger $api): void
    {
        $paths = array_column($api->paths ?: [], 'path');

        static::assertNotContains(
            $path,
            $paths,
            sprintf('Failed asserting that path "%s" does not exist.', $path)
        );
    }

    public function assertHasResponse(string $responseCode, Operation $operation): void
    {
        $responses = array_column($operation->responses ?: [], 'response');

        static::assertContains(
            $responseCode,
            $responses,
            sprintf('Failed asserting that response "%s" does exist.', $responseCode)
        );
    }

    public function assertHasParameter($name, $in, AbstractAnnotation $annotation): void
    {
        /* @var Operation|Swagger $annotation */
        $parameters = array_column($annotation->parameters ?: [], 'name', 'in');

        static::assertContains(
            $name,
            $parameters[$in] ?? [],
            sprintf('Failed asserting that parameter "%s" in "%s" does exist.', $name, $in)
        );
    }

    public function assertNotHasParameter($name, $in, AbstractAnnotation $annotation): void
    {
        /* @var Operation|Swagger $annotation */
        $parameters = array_column($annotation->parameters ?: [], 'name', 'in');

        static::assertNotContains(
            $name,
            $parameters[$in] ?? [],
            sprintf('Failed asserting that parameter "%s" in "%s" does not exist.', $name, $in)
        );
    }

    public function assertHasProperty($property, AbstractAnnotation $annotation): void
    {
        /* @var Definition|Schema|Property|Items $annotation */
        $properties = array_column($annotation->properties ?: [], 'property');

        static::assertContains(
            $property,
            $properties,
            sprintf('Failed asserting that property "%s" does exist.', $property)
        );
    }

    public function toArray(AbstractAnnotation $obj)
    {
        return json_decode(json_encode($obj), true);
    }

    protected static function createKernel(array $options = [])
    {
        return new TestKernel();
    }

    protected function getSwaggerDefinition($area = 'default')
    {
        static::createClient([], ['HTTP_HOST' => 'api.example.com']);

        return static::$kernel->getContainer()->get(sprintf('nelmio_api_doc.generator.%s', $area))->generate();
    }

    protected function getModel($name): Schema
    {
        $api = $this->getSwaggerDefinition();

        $key = array_search($name, array_column($api->definitions, 'definition'), true);
        static::assertNotFalse($key, sprintf('Model "%s" does not exist.', $name));

        return $api->definitions[$key];
    }

    protected function getPath($path): Path
    {
        $api = $this->getSwaggerDefinition();
        $this->assertHasPath($path, $api);

        return $api->paths[array_search($path, array_column($api->paths, 'path'), true)];
    }

    protected function getOperation($path, $method): Operation
    {
        $path = $this->getPath($path);

        $this->assertInstanceOf(
            Operation::class,
            $path->{$method},
            sprintf('Operation "%s" for path "%s" does not exist', $method, $path->path)
        );

        return $path->{$method};
    }

    protected function getResponse(Operation $operation, $response): Response
    {
        $this->assertHasResponse($response, $operation);
        $key = array_search($response, array_column($operation->responses, 'response'), true);

        return $operation->responses[$key];
    }

    protected function getProperty(Schema $annotation, $property): Property
    {
        $this->assertHasProperty($property, $annotation);
        $key = array_search($property, array_column($annotation->properties, 'property'), true);

        return $annotation->properties[$key];
    }

    protected function getParameter(AbstractAnnotation $annotation, $name, $in): Parameter
    {
        /* @var Operation|Swagger $annotation */
        $this->assertHasParameter($name, $in, $annotation);
        $parameters = array_filter($annotation->parameters ?: [], function (Parameter $parameter) use ($name, $in) {
            return $parameter->name === $name && $parameter->in === $in;
        });

        return array_values($parameters)[0];
    }
}
