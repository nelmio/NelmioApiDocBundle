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
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel();
    }

    protected function getOpenApiDefinition($area = 'default'): OA\OpenApi
    {
        return static::$kernel->getContainer()->get(sprintf('nelmio_api_doc.generator.%s', $area))->generate();
    }

    public function hasModel(string $name): bool
    {
        $api = $this->getOpenApiDefinition();
        $key = array_search($name, array_column($api->components->schemas, 'schema'));

        return false !== $key;
    }

    protected function getModel($name): OA\Schema
    {
        $api = $this->getOpenApiDefinition();
        $key = array_search($name, array_column($api->components->schemas, 'schema'));
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

    protected function getOperationResponse(OA\Operation $operation, $response): OA\Response
    {
        $this->assertHasResponse($response, $operation);
        $key = array_search($response, array_column($operation->responses, 'response'));

        return $operation->responses[$key];
    }

    protected function getProperty(OA\Schema $annotation, $property): OA\Property
    {
        $this->assertHasProperty($property, $annotation);
        $key = array_search($property, array_column($annotation->properties, 'property'));

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

    protected function getPath($path): OA\PathItem
    {
        $api = $this->getOpenApiDefinition();
        self::assertHasPath($path, $api);

        return $api->paths[array_search($path, array_column($api->paths, 'path'))];
    }

    public function assertHasPath($path, OA\OpenApi $api)
    {
        $paths = array_column(Generator::UNDEFINED !== $api->paths ? $api->paths : [], 'path');
        static::assertContains(
            $path,
            $paths,
            sprintf('Failed asserting that path "%s" does exist.', $path)
        );
    }

    public function assertNotHasPath($path, OA\OpenApi $api)
    {
        $paths = array_column(Generator::UNDEFINED !== $api->paths ? $api->paths : [], 'path');
        static::assertNotContains(
            $path,
            $paths,
            sprintf('Failed asserting that path "%s" does not exist.', $path)
        );
    }

    public function assertHasResponse($responseCode, OA\Operation $operation)
    {
        $responses = array_column(Generator::UNDEFINED !== $operation->responses ? $operation->responses : [], 'response');
        static::assertContains(
            $responseCode,
            $responses,
            sprintf('Failed asserting that response "%s" does exist.', $responseCode)
        );
    }

    public function assertHasParameter($name, $in, OA\AbstractAnnotation $annotation)
    {
        /* @var OA\Operation|OA\OpenApi $annotation */
        $parameters = array_filter(Generator::UNDEFINED !== $annotation->parameters ? $annotation->parameters : [], function (OA\Parameter $parameter) use ($name, $in) {
            return $parameter->name === $name && $parameter->in === $in;
        });

        static::assertNotEmpty(
            $parameters,
            sprintf('Failed asserting that parameter "%s" in "%s" does exist.', $name, $in)
        );
    }

    public function assertNotHasParameter($name, $in, OA\AbstractAnnotation $annotation)
    {
        /* @var OA\Operation|OA\OpenApi $annotation */
        $parameters = array_column(Generator::UNDEFINED !== $annotation->parameters ? $annotation->parameters : [], 'name', 'in');
        static::assertNotContains(
            $name,
            $parameters[$in] ?? [],
            sprintf('Failed asserting that parameter "%s" in "%s" does not exist.', $name, $in)
        );
    }

    public function assertHasProperty($property, OA\AbstractAnnotation $annotation)
    {
        /* @var OA\Schema|OA\Property|OA\Items $annotation */
        $properties = array_column(Generator::UNDEFINED !== $annotation->properties ? $annotation->properties : [], 'property');
        static::assertContains(
            $property,
            $properties,
            sprintf('Failed asserting that property "%s" does exist.', $property)
        );
    }

    public function assertNotHasProperty($property, OA\AbstractAnnotation $annotation)
    {
        /* @var OA\Schema|OA\Property|OA\Items $annotation */
        $properties = array_column(Generator::UNDEFINED !== $annotation->properties ? $annotation->properties : [], 'property');
        static::assertNotContains(
            $property,
            $properties,
            sprintf('Failed asserting that property "%s" does not exist.', $property)
        );
    }

    /**
     * BC symfony < 5.3 and symfony >= 7.
     *
     * KernelTestCase::getContainer has a Container return type object in symfony 7
     * which is incompatible with the return type of previous versions or
     * at least the return type of overridden method (which was added for BC compatibility),
     * hence moving it to the magic method.
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if ('getContainer' === $name) {
            return static::$container;
        }

        return null;
    }
}
