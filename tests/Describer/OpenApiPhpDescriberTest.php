<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Describer;

use Nelmio\ApiDocBundle\Describer\OpenApiPhpDescriber;
use Nelmio\ApiDocBundle\Describer\OperationIdGeneration;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAT;
use OpenApi\Context;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class OpenApiPhpDescriberTest extends TestCase
{
    public function testOtherAttributesAreKeptInContextByDefault(): void
    {
        $api = $this->describe(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Serialization of 'Closure' is not allowed");

        serialize($api);
    }

    public function testOtherAttributesAreIgnored(): void
    {
        $api = $this->describe(true);

        $operation = $api->paths[0]->get;
        self::assertInstanceOf(OA\Get::class, $operation);
        self::assertSame('Foo', $operation->summary);
        self::assertSame(200, $operation->responses[0]->response);

        self::assertInstanceOf(OA\OpenApi::class, unserialize(serialize($api)));
    }

    private function describe(bool $ignoreOtherAttributes): OA\OpenApi
    {
        $routes = new RouteCollection();
        $routes->add('foo', new Route('/foo', ['_controller' => OpenApiPhpDescriberController::class.'::foo'], methods: ['GET']));

        $describer = new OpenApiPhpDescriber(
            $routes,
            new ControllerReflector(new Container()),
            OperationIdGeneration::ALWAYS_PREPEND,
            $ignoreOtherAttributes,
        );

        $api = new OA\OpenApi(['_context' => new Context()]);
        $describer->describe($api);

        return $api;
    }
}

#[\Attribute(\Attribute::TARGET_METHOD)]
final class OpenApiPhpDescriberNonSerializableAttribute
{
    public \Closure $closure;

    public function __construct()
    {
        $this->closure = static fn (): bool => true;
    }
}

final class OpenApiPhpDescriberController
{
    #[OAT\Get(summary: 'Foo')]
    #[OAT\Response(response: 200, description: 'OK')]
    #[OpenApiPhpDescriberNonSerializableAttribute]
    public function foo(): void
    {
    }
}
