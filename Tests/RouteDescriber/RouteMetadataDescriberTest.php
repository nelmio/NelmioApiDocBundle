<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\RouteDescriber;

use Nelmio\ApiDocBundle\RouteDescriber\RouteMetadataDescriber;
use OpenApi\Annotations\OpenApi;
use OpenApi\Context;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\Routing\Route;

class RouteMetadataDescriberTest extends TestCase
{
    public function testUndefinedCheck()
    {
        $routeDescriber = new RouteMetadataDescriber();

        $this->assertNull($routeDescriber->describe(new OpenApi(['_context' => new Context()]), new Route('foo'), new ReflectionMethod(__CLASS__, 'testUndefinedCheck')));
    }

    public function testRouteRequirementsWithPattern()
    {
        $api = new OpenApi([]);
        $routeDescriber = new RouteMetadataDescriber();
        $route = new Route('/index/{bar}/{foo}.html', [], ['foo' => '[0-9]|[a-z]'], [], 'localhost', 'https', ['GET']);
        $routeDescriber->describe(
            $api,
            $route,
            new ReflectionMethod(__CLASS__, 'testRouteRequirementsWithPattern')
        );

        $this->assertEquals('/index/{bar}/{foo}.html', $api->paths[0]->path);
        $getPathParameter = $api->paths[0]->get->parameters[1];
        if ('foo' === $getPathParameter->name) {
            $this->assertEquals('path', $getPathParameter->in);
            $this->assertEquals('foo', $getPathParameter->name);
            $this->assertEquals('string', $getPathParameter->schema->type);
            $this->assertEquals('[0-9]|[a-z]', $getPathParameter->schema->pattern);
        }
    }

    /**
     * @dataProvider provideEnumPattern
     */
    public function testSimpleOrRequirementsAreHandledAsEnums($req)
    {
        $api = new OpenApi([]);
        $routeDescriber = new RouteMetadataDescriber();
        $route = new Route('/index/{bar}/{foo}.html', [], ['foo' => $req], [], 'localhost', 'https', ['GET']);
        $routeDescriber->describe(
            $api,
            $route,
            new ReflectionMethod(__CLASS__, 'testSimpleOrRequirementsAreHandledAsEnums')
        );

        $this->assertEquals('/index/{bar}/{foo}.html', $api->paths[0]->path);
        $getPathParameter = $api->paths[0]->get->parameters[1];
        $this->assertEquals('path', $getPathParameter->in);
        $this->assertEquals('foo', $getPathParameter->name);
        $this->assertEquals('string', $getPathParameter->schema->type);
        $this->assertEquals(explode('|', $req), $getPathParameter->schema->enum);
        $this->assertEquals($req, $getPathParameter->schema->pattern);
    }

    /**
     * @dataProvider provideInvalidEnumPattern
     */
    public function testNonEnumPatterns($pattern)
    {
        $api = new OpenApi([]);
        $routeDescriber = new RouteMetadataDescriber();
        $route = new Route('/index/{foo}.html', [], ['foo' => $pattern], [], 'localhost', 'https', ['GET']);
        $routeDescriber->describe(
            $api,
            $route,
            new ReflectionMethod(__CLASS__, 'testNonEnumPatterns')
        );

        $getPathParameter = $api->paths[0]->get->parameters[0];
        $this->assertEquals($pattern, $getPathParameter->schema->pattern);
        $this->assertEquals(Generator::UNDEFINED, $getPathParameter->schema->enum);
    }

    public function provideEnumPattern()
    {
        yield ['1|2|3'];
        yield ['srf|rtr|rsi'];
        yield ['srf-1|srf-2'];
        yield ['srf-1|srf-2'];
    }

    public function provideInvalidEnumPattern()
    {
        yield ['|'];
        yield ['|a'];
        yield ['srf|*|rtr'];
        yield ['srf||rtr'];
        yield ['1|2|'];
        yield ['/1|2/'];
        yield ['\d|a'];
        // dots have special meaning and should be skipped
        yield ['a_1\.html|b-2\.html'];
        yield ['a_1.html|b-2.html'];
    }
}
