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

use Generator;
use Nelmio\ApiDocBundle\RouteDescriber\SymfonyDescriber;
use OpenApi\Annotations\OpenApi;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionParameter;
use stdClass;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Route;

class SymfonyDescriberTest extends TestCase
{
    private $symfonyDescriber;

    protected function setUp(): void
    {
        if (
            !class_exists(MapRequestPayload::class)
            && !class_exists(MapQueryParameter::class)
            && !class_exists(MapQueryString::class)
        ) {
            $this->markTestSkipped('Symfony 6.3 attributes not found');
        }

        $this->symfonyDescriber = new SymfonyDescriber();
    }

    /**
     * @dataProvider provideMapRequestPayloadTestData
     *
     * @requires PHP >= 8
     *
     * @param string[] $expectedMediaTypes
     */
    public function testMapRequestPayloadParamRegistersRequestBody(
        MapRequestPayload $mapRequestPayload,
        array $expectedMediaTypes
    ): void {
        $classType = stdClass::class;

        $reflectionNamedType = $this->createStub(ReflectionNamedType::class);
        $reflectionNamedType->method('getName')->willReturn($classType);

        $api = new OpenApi([]);

        $controllerMethodMock = $this->createStub(\ReflectionMethod::class);

        $reflectionAttributeMock = $this->createStub(ReflectionAttribute::class);
        $reflectionAttributeMock->method('getName')->willReturn(MapRequestPayload::class);
        $reflectionAttributeMock->method('newInstance')->willReturn($mapRequestPayload);

        $reflectionParameterStub= $this->createStub(ReflectionParameter::class);
        $reflectionParameterStub->method('getType')->willReturn($reflectionNamedType);
        $reflectionParameterStub->method('getAttributes')->willReturn([$reflectionAttributeMock]);

        $controllerMethodMock->method('getParameters')->willReturn([$reflectionParameterStub]);

        $this->symfonyDescriber->describe(
            $api,
            new Route('/'),
            $controllerMethodMock
        );

        foreach ($expectedMediaTypes as $expectedMediaType) {
            $requestBodyContent = $api->paths[0]->get->requestBody->content[$expectedMediaType];

            self::assertSame($expectedMediaType, $requestBodyContent->mediaType);
            self::assertSame('object', $requestBodyContent->schema->type);
            self::assertSame($classType, $requestBodyContent->schema->ref->type);
        }
    }

    public function provideMapRequestPayloadTestData(): Generator
    {
        yield 'it sets default mediaType to json' => [
            new MapRequestPayload(),
            ['application/json'],
        ];

        yield 'it sets the mediaType to json' => [
            new MapRequestPayload('json'),
            ['application/json'],
        ];

        yield 'it sets the mediaType to xml' => [
            new MapRequestPayload('xml'),
            ['application/xml'],
        ];

        yield 'it sets multiple mediaTypes' => [
            new MapRequestPayload(['json', 'xml']),
            ['application/json', 'application/xml'],
        ];
    }
}
