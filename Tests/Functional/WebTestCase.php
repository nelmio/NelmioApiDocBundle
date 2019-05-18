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
    protected static $openApiDefinition;

    /**
     * @param string     $path
     * @param OA\OpenApi $api
     */
    public function assertHasPath($path, OA\OpenApi $api)
    {
        $paths = array_column($api->paths ?: [], 'path');

        static::assertContains(
            $path,
            $paths,
            sprintf('Failed asserting that path "%s" does exist.', $path)
        );
    }

    /**
     * @param string     $path
     * @param OA\OpenApi $api
     */
    public function assertNotHasPath($path, OA\OpenApi $api)
    {
        $paths = array_column($api->paths ?: [], 'path');

        static::assertNotContains(
            $path,
            $paths,
            sprintf('Failed asserting that path "%s" does not exist.', $path)
        );
    }

    /**
     * @param string       $responseCode
     * @param OA\Operation $operation
     */
    public function assertHasResponse($responseCode, OA\Operation $operation)
    {
        $responses = array_column($operation->responses ?: [], 'response');

        static::assertContains(
            $responseCode,
            $responses,
            sprintf('Failed asserting that response "%s" does exist.', $responseCode)
        );
    }

    public function assertHasParameter($name, $in, OA\AbstractAnnotation $annotation)
    {
        /* @var OA\Operation|OA\OpenApi $annotation */
        $parameters = array_column($annotation->parameters ?: [], 'name', 'in');

        static::assertContains(
            $name,
            $parameters[$in] ?? [],
            sprintf('Failed asserting that parameter "%s" in "%s" does exist.', $name, $in)
        );
    }

    public function assertNotHasParameter($name, $in, OA\AbstractAnnotation $annotation)
    {
        /* @var OA\Operation|OA\OpenApi $annotation */
        $parameters = array_column($annotation->parameters ?: [], 'name', 'in');

        static::assertNotContains(
            $name,
            $parameters[$in] ?? [],
            sprintf('Failed asserting that parameter "%s" in "%s" does not exist.', $name, $in)
        );
    }

    public function assertHasProperty($property, OA\AbstractAnnotation $annotation)
    {
        /* @var OA\Schema|OA\Property|OA\Items $annotation */
        $properties = array_column($annotation->properties ?: [], 'property');

        static::assertContains(
            $property,
            $properties,
            sprintf('Failed asserting that property "%s" does exist.', $property)
        );
    }

    public function toArray(OA\AbstractAnnotation $obj)
    {
        return json_decode(json_encode($obj), true);
    }

    protected static function createKernel(array $options = [])
    {
        return new TestKernel();
    }

    /**
     * @param string $area
     *
     * @return OA\OpenApi
     */
    protected function getOpenApiDefinition($area = 'default')
    {
        static::createClient([], ['HTTP_HOST' => 'api.example.com']);

        return static::$kernel->getContainer()->get(sprintf('nelmio_api_doc.generator.%s', $area))->generate();
    }

    protected function getModel($name): OA\Schema
    {
        $api = $this->getOpenApiDefinition();

        $key = array_search($name, array_column($api->components->schemas, 'schema'), true);
        static::assertNotFalse($key, sprintf('Model "%s" does not exist.', $name));

        return $api->components->schemas[$key];
    }

    protected function getPath($path): OA\PathItem
    {
        $api = $this->getOpenApiDefinition();
        $this->assertHasPath($path, $api);

        return $api->paths[array_search($path, array_column($api->paths, 'path'), true)];
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

    protected function getResponse(OA\Operation $operation, $response): OA\Response
    {
        $this->assertHasResponse($response, $operation);
        $key = array_search($response, array_column($operation->responses, 'response'), true);

        return $operation->responses[$key];
    }

    protected function getProperty(OA\Schema $annotation, $property): OA\Property
    {
        $this->assertHasProperty($property, $annotation);
        $key = array_search($property, array_column($annotation->properties, 'property'), true);

        return $annotation->properties[$key];
    }

    protected function getParameter(OA\AbstractAnnotation $annotation, $name, $in): OA\Parameter
    {
        /* @var OA\Operation|OA\OpenApi $annotation */
        $this->assertHasParameter($name, $in, $annotation);
        $parameters = array_filter($annotation->parameters ?: [], function (OA\Parameter $parameter) use ($name, $in) {
            return $parameter->name === $name && $parameter->in === $in;
        });

        return array_values($parameters)[0];
    }
}
