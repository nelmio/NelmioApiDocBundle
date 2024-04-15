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
use OpenApi\Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class WebTestCase extends BaseWebTestCase
{
    /**
     * @param array<mixed> $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel();
    }

    protected function getOpenApiDefinition(string $area = 'default'): OA\OpenApi
    {
        return static::$kernel->getContainer()->get(sprintf('nelmio_api_doc.generator.%s', $area))->generate();
    }

    public function hasModel(string $name): bool
    {
        $api = $this->getOpenApiDefinition();
        $key = array_search($name, array_column($api->components->schemas, 'schema'), true);

        return false !== $key;
    }

    protected function getModel(string $name): OA\Schema
    {
        $api = $this->getOpenApiDefinition();
        $key = array_search($name, array_column($api->components->schemas, 'schema'), true);
        static::assertNotFalse($key, sprintf('Model "%s" does not exist.', $name));

        return $api->components->schemas[$key];
    }

    protected function getOperation(string $path, string $method): OA\Operation
    {
        $path = $this->getPath($path);

        self::assertInstanceOf(
            OA\Operation::class,
            $path->{$method},
            sprintf('Operation "%s" for path "%s" does not exist', $method, $path->path)
        );

        return $path->{$method};
    }

    /**
     * @param int|string $response
     */
    protected function getOperationResponse(OA\Operation $operation, $response): OA\Response
    {
        $this->assertHasResponse($response, $operation);
        $key = array_search($response, array_column($operation->responses, 'response'), true);

        return $operation->responses[$key];
    }

    protected function getProperty(OA\Schema $annotation, string $property): OA\Property
    {
        $this->assertHasProperty($property, $annotation);
        $key = array_search($property, array_column($annotation->properties, 'property'), true);

        return $annotation->properties[$key];
    }

    /**
     * @param OA\Operation|OA\OpenApi $annotation
     */
    protected function getParameter(OA\AbstractAnnotation $annotation, string $name, string $in): OA\Parameter
    {
        $this->assertHasParameter($name, $in, $annotation);
        $parameters = array_filter($annotation->parameters ?? [], function (OA\Parameter $parameter) use ($name, $in) {
            return $parameter->name === $name && $parameter->in === $in;
        });

        return array_values($parameters)[0];
    }

    protected function getPath(string $path): OA\PathItem
    {
        $api = $this->getOpenApiDefinition();
        self::assertHasPath($path, $api);

        return $api->paths[array_search($path, array_column($api->paths, 'path'), true)];
    }

    public function assertHasPath(string $path, OA\OpenApi $api): void
    {
        $paths = array_column(Generator::UNDEFINED !== $api->paths ? $api->paths : [], 'path');
        static::assertContains(
            $path,
            $paths,
            sprintf('Failed asserting that path "%s" does exist.', $path)
        );
    }

    public function assertNotHasPath(string $path, OA\OpenApi $api): void
    {
        $paths = array_column(Generator::UNDEFINED !== $api->paths ? $api->paths : [], 'path');
        static::assertNotContains(
            $path,
            $paths,
            sprintf('Failed asserting that path "%s" does not exist.', $path)
        );
    }

    /**
     * @param int|string $responseCode
     */
    public function assertHasResponse($responseCode, OA\Operation $operation): void
    {
        $responses = array_column(Generator::UNDEFINED !== $operation->responses ? $operation->responses : [], 'response');
        static::assertContains(
            $responseCode,
            $responses,
            sprintf('Failed asserting that response "%s" does exist.', $responseCode)
        );
    }

    /**
     * @param OA\Operation|OA\OpenApi $annotation
     */
    public function assertHasParameter(string $name, string $in, OA\AbstractAnnotation $annotation): void
    {
        $parameters = array_filter(Generator::UNDEFINED !== $annotation->parameters ? $annotation->parameters : [], function (OA\Parameter $parameter) use ($name, $in) {
            return $parameter->name === $name && $parameter->in === $in;
        });

        static::assertNotEmpty(
            $parameters,
            sprintf('Failed asserting that parameter "%s" in "%s" does exist.', $name, $in)
        );
    }

    /**
     * @param OA\Operation|OA\OpenApi $annotation
     */
    public function assertNotHasParameter(string $name, string $in, OA\AbstractAnnotation $annotation): void
    {
        $parameters = array_column(Generator::UNDEFINED !== $annotation->parameters ? $annotation->parameters : [], 'name', 'in');
        static::assertNotContains(
            $name,
            $parameters[$in] ?? [],
            sprintf('Failed asserting that parameter "%s" in "%s" does not exist.', $name, $in)
        );
    }

    /**
     * @param OA\Schema|OA\Property|OA\Items $annotation
     */
    public function assertHasProperty(string $property, OA\AbstractAnnotation $annotation): void
    {
        $properties = array_column(Generator::UNDEFINED !== $annotation->properties ? $annotation->properties : [], 'property');
        static::assertContains(
            $property,
            $properties,
            sprintf('Failed asserting that property "%s" does exist.', $property)
        );
    }

    /**
     * @param OA\Schema|OA\Property|OA\Items $annotation
     */
    public function assertNotHasProperty(string $property, OA\AbstractAnnotation $annotation): void
    {
        $properties = array_column(Generator::UNDEFINED !== $annotation->properties ? $annotation->properties : [], 'property');
        static::assertNotContains(
            $property,
            $properties,
            sprintf('Failed asserting that property "%s" does not exist.', $property)
        );
    }
}
