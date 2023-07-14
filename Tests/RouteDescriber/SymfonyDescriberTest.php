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

use Nelmio\ApiDocBundle\RouteDescriber\SymfonyDescriber;
use OpenApi\Annotations\OpenApi;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use stdClass;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
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
        ) {
            self::markTestSkipped('Symfony 6.3 attributes not found');
        }

        $this->symfonyDescriber = new SymfonyDescriber();
    }

    /**
     * @dataProvider provideMapRequestPayloadTestData
     *
     * @param string[] $expectedMediaTypes
     */
    public function testMapRequestPayload(object $controllerClass, array $expectedMediaTypes): void
    {
        $api = new OpenApi([]);

        $controllerReflectionMethod = new \ReflectionMethod($controllerClass, 'route');

        $this->symfonyDescriber->describe(
            $api,
            new Route('/'),
            $controllerReflectionMethod
        );

        foreach ($expectedMediaTypes as $expectedMediaType) {
            $requestBodyContent = $api->paths[0]->get->requestBody->content[$expectedMediaType];

            self::assertSame($expectedMediaType, $requestBodyContent->mediaType);
            self::assertSame('object', $requestBodyContent->schema->type);
            self::assertSame(stdClass::class, $requestBodyContent->schema->ref->type);
        }
    }

    public static function provideMapRequestPayloadTestData(): iterable
    {
        yield 'it sets default mediaType to json' => [
            new class() {
                public function route(
                    #[MapRequestPayload] stdClass $payload
                ) {
                }
            },
            ['application/json'],
        ];

        yield 'it sets mediaType to json' => [
            new class() {
                public function route(
                    #[MapRequestPayload('json')] stdClass $payload
                ) {
                }
            },
            ['application/json'],
        ];

        yield 'it sets mediaType to xml' => [
            new class() {
                public function route(
                    #[MapRequestPayload('xml')] stdClass $payload
                ) {
                }
            },
            ['application/xml'],
        ];

        yield 'it sets multiple mediaTypes' => [
            new class() {
                public function route(
                    #[MapRequestPayload(['json', 'xml'])] stdClass $payload
                ) {
                }
            },
            ['application/json', 'application/xml'],
        ];
    }

    /**
     * @dataProvider provideMapQueryParameterTestData
     */
    public function testMapQueryParameter(object $controllerClass): void
    {
        $api = new OpenApi([]);

        $controllerReflectionMethod = new \ReflectionMethod($controllerClass, 'route');

        $this->symfonyDescriber->describe(
            $api,
            new Route('/'),
            $controllerReflectionMethod
        );

        foreach ($controllerReflectionMethod->getParameters() as $key => $parameter) {
            /** @var MapQueryParameter $mapQueryParameter */
            $mapQueryParameter = $parameter->getAttributes(MapQueryParameter::class, ReflectionAttribute::IS_INSTANCEOF)[0]->newInstance();

            $documentationParameter = $api->paths[0]->get->parameters[$key];
            self::assertSame($mapQueryParameter->name ?? $parameter->getName(), $documentationParameter->name);
            self::assertSame('query', $documentationParameter->in);
            self::assertSame(!$parameter->isDefaultValueAvailable() && !$parameter->allowsNull(), $documentationParameter->required);

            $schema = $documentationParameter->schema;
            self::assertSame($parameter->getType()->getName(), $schema->type);
            if ($parameter->isDefaultValueAvailable()) {
                self::assertSame($parameter->getDefaultValue(), $schema->default);
            }

            if (FILTER_VALIDATE_REGEXP === $mapQueryParameter->filter) {
                self::assertSame($mapQueryParameter->options['regexp'], $schema->pattern);
            }
        }
    }

    public static function provideMapQueryParameterTestData(): iterable
    {
        yield 'it documents query parameters' => [new class() {
            public function route(
                #[MapQueryParameter] int $parameter1,
                #[MapQueryParameter] int $parameter2
            ) {
            }
        }];

        yield 'it documents query parameters with default values' => [new class() {
            public function route(
                #[MapQueryParameter] int $parameter1 = 123,
                #[MapQueryParameter] int $parameter2 = 456
            ) {
            }
        }];

        yield 'it documents query parameters with nullable types' => [new class() {
            public function route(
                #[MapQueryParameter] ?int $parameter1,
                #[MapQueryParameter] ?int $parameter2
            ) {
            }
        }];

        yield 'it uses MapQueryParameter name argument as name' => [new class() {
            public function route(
                #[MapQueryParameter('someOtherParameter1Name')] int $parameter1,
                #[MapQueryParameter('someOtherParameter2Name')] int $parameter2
            ) {
            }
        }];

        yield 'it uses documents regex pattern' => [new class() {
            public function route(
                #[MapQueryParameter(filter: FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^\d+$/'])] int $parameter1,
                #[MapQueryParameter(filter: FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^\d+$/'])] int $parameter2
            ) {
            }
        }];
    }
}
