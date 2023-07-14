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
        if (\PHP_VERSION_ID < 80100) {
            self::markTestSkipped('Attributes require PHP 8');
        }

        if (
            !class_exists(MapRequestPayload::class)
            && !class_exists(MapQueryParameter::class)
            && !class_exists(MapQueryString::class)
        ) {
            self::markTestSkipped('Symfony 6.3 attributes not found');
        }

        $this->symfonyDescriber = new SymfonyDescriber();
    }

    public function testMapRequestPayload(): void
    {
        foreach (self::provideMapRequestPayloadTestData() as $testData) {
            $this->testMapRequestPayloadParamRegistersRequestBody(...$testData);
        }
    }

    /**
     * @param string[] $expectedMediaTypes
     */
    private function testMapRequestPayloadParamRegistersRequestBody(
        MapRequestPayload $mapRequestPayload,
        array $expectedMediaTypes
    ): void {
        $classType = stdClass::class;

        $reflectionNamedType = $this->createStub(ReflectionNamedType::class);
        $reflectionNamedType->method('getName')->willReturn($classType);

        $api = new OpenApi([]);

        $controllerMethodMock = $this->createStub(\ReflectionMethod::class);

        $reflectionAttributeStub = $this->createStub(ReflectionAttribute::class);
        $reflectionAttributeStub->method('getName')->willReturn(MapRequestPayload::class);
        $reflectionAttributeStub->method('newInstance')->willReturn($mapRequestPayload);

        $reflectionParameterStub = $this->createMock(ReflectionParameter::class);
        $reflectionParameterStub->method('getType')->willReturn($reflectionNamedType);
        $reflectionParameterStub
            ->expects(self::atLeastOnce())
            ->method('getAttributes')
            ->willReturnCallback(static function (string $argument) use ($reflectionAttributeStub) {
                if (MapRequestPayload::class === $argument) {
                    return [$reflectionAttributeStub];
                }

                return [];
            })
        ;

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

    public static function provideMapRequestPayloadTestData(): Generator
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

    public function testMapQueryParameter(): void
    {
        foreach (self::provideMapQueryParameterTestData() as $testData) {
            $this->testMapQueryParameterParamRegistersParameter(...$testData);
        }
    }

    /**
     * @param array<array{instance: MapQueryParameter, type: string, name: string, defaultValue?: mixed}> $mapQueryParameterDataCollection
     */
    private function testMapQueryParameterParamRegistersParameter(array $mapQueryParameterDataCollection): void
    {
        $api = new OpenApi([]);

        $reflectionParameters = [];
        foreach ($mapQueryParameterDataCollection as $mapQueryParameterData) {
            $reflectionNamedType = $this->createStub(ReflectionNamedType::class);
            $reflectionNamedType->method('isBuiltin')->willReturn(true);
            $reflectionNamedType->method('getName')->willReturn($mapQueryParameterData['type']);

            $reflectionAttributeStub = $this->createStub(ReflectionAttribute::class);
            $reflectionAttributeStub->method('getName')->willReturn($mapQueryParameterData['name']);
            $reflectionAttributeStub->method('newInstance')->willReturn($mapQueryParameterData['instance']);

            $reflectionParameterStub= $this->createStub(ReflectionParameter::class);
            $reflectionParameterStub->method('getName')->willReturn($mapQueryParameterData['name']);
            $reflectionParameterStub->method('getType')->willReturn($reflectionNamedType);
            $reflectionParameterStub
                ->expects(self::atLeastOnce())
                ->method('getAttributes')
                ->willReturnCallback(static function (string $argument) use ($reflectionAttributeStub) {
                    if (MapQueryParameter::class === $argument) {
                        return [$reflectionAttributeStub];
                    }

                    return [];
                })
            ;

            if (isset($mapQueryParameterData['defaultValue'])) {
                $reflectionParameterStub->method('isDefaultValueAvailable')->willReturn(true);
                $reflectionParameterStub->method('getDefaultValue')->willReturn($mapQueryParameterData['defaultValue']);
            } else {
                $reflectionParameterStub->method('isDefaultValueAvailable')->willReturn(false);
                $reflectionParameterStub->method('getDefaultValue')->willThrowException(new \ReflectionException());
            }

            $reflectionParameters[] = $reflectionParameterStub;
        }

        $controllerMethodMock = $this->createStub(\ReflectionMethod::class);
        $controllerMethodMock->method('getParameters')->willReturn($reflectionParameters);

        $this->symfonyDescriber->describe(
            $api,
            new Route('/'),
            $controllerMethodMock
        );

        foreach ($mapQueryParameterDataCollection as $key => $mapQueryParameterData) {
            $parameter = $api->paths[0]->get->parameters[$key];

            self::assertSame($mapQueryParameterData['instance']->name ?? $mapQueryParameterData['name'], $parameter->name);
            self::assertSame('query', $parameter->in);
            self::assertSame(!$reflectionParameters[$key]->isDefaultValueAvailable() && !$reflectionParameters[$key]->allowsNull(), $parameter->required);

            $schema = $parameter->schema;
            self::assertSame($mapQueryParameterData['type'], $schema->type);
            if (isset($mapQueryParameterData['defaultValue'])) {
                self::assertSame($mapQueryParameterData['defaultValue'], $schema->default);
            }
        }
    }

    public static function provideMapQueryParameterTestData(): Generator
    {
        yield 'it sets a single query parameter' => [
            [
                [
                    'instance' => new MapQueryParameter(),
                    'type' => 'int',
                    'name' => 'parameter1',
                ],
            ],
        ];

        yield 'it sets two query parameters' => [
            [
                [
                    'instance' => new MapQueryParameter(),
                    'type' => 'int',
                    'name' => 'parameter1',
                ],
                [
                    'instance' => new MapQueryParameter(),
                    'type' => 'int',
                    'name' => 'parameter2',
                ],
            ],
        ];

        yield 'it sets a single query parameter with default value' => [
            [
                [
                    'instance' => new MapQueryParameter(),
                    'type' => 'string',
                    'name' => 'parameterDefault',
                    'defaultValue' => 'Some default value',
                ],
            ],
        ];

        yield 'it uses MapQueryParameter manually defined name' => [
            [
                [
                    'instance' => new MapQueryParameter('name'),
                    'type' => 'string',
                    'name' => 'parameter',
                ],
            ],
        ];
    }
}
