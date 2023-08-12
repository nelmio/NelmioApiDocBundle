<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\RouteDescriber\SymfonyAnnotationDescriber;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\RouteDescriber\SymfonyAnnotationDescriber\SymfonyMapRequestPayloadDescriber;
use OpenApi\Annotations\OpenApi;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use stdClass;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use const PHP_VERSION_ID;

class SymfonyMapRequestPayloadDescriberTest extends TestCase
{
    /**
     * @var SymfonyMapRequestPayloadDescriber $symfonyMapRequestPayloadDescriber
     */
    private $symfonyMapRequestPayloadDescriber;

    protected function setUp(): void
    {
        if (PHP_VERSION_ID < 80100) {
            self::markTestSkipped('Attributes require PHP 8');
        }

        if (!class_exists(MapRequestPayload::class)) {
            self::markTestSkipped('Symfony 6.3 MapRequestPayload attribute not found');
        }

        $this->symfonyMapRequestPayloadDescriber = new SymfonyMapRequestPayloadDescriber();
    }

    /**
     * @dataProvider provideMapRequestPayloadTestData
     *
     * @param string[] $expectedMediaTypes
     */
    public function testMapRequestPayload(callable $function, array $expectedMediaTypes): void
    {
        $parameter = new ReflectionParameter($function, 'payload');

        $this->symfonyMapRequestPayloadDescriber->describe(
            new OpenApi([]),
            $operation = new Operation([]),
            $parameter
        );

        foreach ($expectedMediaTypes as $expectedMediaType) {
            $requestBodyContent = $operation->requestBody->content[$expectedMediaType];

            self::assertSame($expectedMediaType, $requestBodyContent->mediaType);
            self::assertSame('object', $requestBodyContent->schema->type);
            self::assertSame(stdClass::class, $requestBodyContent->schema->ref->type);
        }
    }

    public static function provideMapRequestPayloadTestData(): iterable
    {
        yield 'it sets default mediaType to json' => [
            function (
                #[MapRequestPayload] stdClass $payload
            ) {
            },
            ['application/json'],
        ];

        yield 'it sets mediaType to json' => [
            function (
                #[MapRequestPayload('json')] stdClass $payload
            ) {
            },
            ['application/json'],
        ];

        yield 'it sets mediaType to xml' => [
            function (
                #[MapRequestPayload('xml')] stdClass $payload
            ) {
            },
            ['application/xml'],
        ];

        yield 'it sets multiple mediaTypes' => [
            function (
                #[MapRequestPayload(['json', 'xml'])] stdClass $payload
            ) {
            },
            ['application/json', 'application/xml'],
        ];
    }
}
