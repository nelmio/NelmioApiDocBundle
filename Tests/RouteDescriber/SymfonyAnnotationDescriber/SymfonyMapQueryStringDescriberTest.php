<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\RouteDescriber\SymfonyAnnotationDescriber;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\ModelDescriber\SelfDescribingModelDescriber;
use Nelmio\ApiDocBundle\RouteDescriber\SymfonyAnnotationDescriber\SymfonyMapQueryStringDescriber;
use Nelmio\ApiDocBundle\Tests\RouteDescriber\Fixtures\DTO;
use Nelmio\ApiDocBundle\Tests\RouteDescriber\Fixtures\SymfonyDescriberMapQueryStringClass;
use OpenApi\Annotations\OpenApi;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use const PHP_VERSION_ID;

class SymfonyMapQueryStringDescriberTest extends TestCase
{
    /**
     * @var OpenApi
     */
    private $openApi;
    /**
     * @var SymfonyMapQueryStringDescriber
     */
    private $symfonyMapQueryStringDescriber;

    protected function setUp(): void
    {
        if (PHP_VERSION_ID < 80100) {
            self::markTestSkipped('Attributes require PHP 8');
        }

        if (!class_exists(MapQueryString::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryString attribute not found');
        }

        $this->openApi = new OpenApi([]);

        $this->symfonyMapQueryStringDescriber = new SymfonyMapQueryStringDescriber([new SelfDescribingModelDescriber()]);

        $registry = new ModelRegistry([], $this->openApi, []);

        $this->symfonyMapQueryStringDescriber->setModelRegistry($registry);
    }

    /**
     * @dataProvider provideMapQueryStringTestData
     *
     * @param array{optional: bool} $expectations
     */
    public function testMapQueryString(callable $function, array $expectations): void
    {
        $parameter = new ReflectionParameter($function, 'parameter1');

        $this->symfonyMapQueryStringDescriber->describe(
            $this->openApi,
            $operation = new Operation([]),
            $parameter
        );

        // Test it registers the model
        $modelSchema = $this->openApi->components->schemas[0];
        $expectedModelProperties = SymfonyDescriberMapQueryStringClass::getProperties();

        self::assertSame(SymfonyDescriberMapQueryStringClass::SCHEMA, $modelSchema->schema);
        self::assertSame(SymfonyDescriberMapQueryStringClass::TITLE, $modelSchema->title);
        self::assertSame(SymfonyDescriberMapQueryStringClass::TYPE, $modelSchema->type);
        self::assertEquals($expectedModelProperties, $modelSchema->properties);

        foreach ($expectedModelProperties as $key => $expectedModelProperty) {
            $queryParameter = $operation->parameters[$key];

            self::assertSame('query', $queryParameter->in);
            self::assertSame($expectedModelProperty->property, $queryParameter->name);
            self::assertSame(!$expectations['optional'], $queryParameter->required);
        }
    }

    public static function provideMapQueryStringTestData(): iterable
    {
        yield 'it documents query string parameters' => [
            function (
                #[MapQueryString] SymfonyDescriberMapQueryStringClass $parameter1,
            ) {
            },
            [
                'optional' => false,
            ],
        ];

        yield 'it documents a nullable type as optional' => [
            function (
                #[MapQueryString] ?SymfonyDescriberMapQueryStringClass $parameter1,
            ) {
            },
            [
                'optional' => true,
            ],
        ];

        yield 'it documents a default value as optional' => [
            function (
                #[MapQueryString] ?SymfonyDescriberMapQueryStringClass $parameter1,
            ) {
            },
            [
                'optional' => true,
            ],
        ];
    }

    public function testItDescribesProperties(): void
    {
        $function = function (
            #[MapQueryString] DTO $DTO,
        ) {
        };

        $parameter = new ReflectionParameter($function, 'DTO');

        $this->symfonyMapQueryStringDescriber->describe(
            $this->openApi,
            $operation = new Operation([]),
            $parameter
        );

        // Test it registers the model
        $modelSchema = $this->openApi->components->schemas[0];
        $expectedModelProperties = DTO::getProperties();

        self::assertEquals($expectedModelProperties, $modelSchema->properties);

        self::assertSame('id', $operation->parameters[0]->name);
        self::assertSame('int', $operation->parameters[0]->schema->type);

        self::assertSame('name', $operation->parameters[1]->name);

        self::assertSame('nullableName', $operation->parameters[2]->name);
        self::assertSame('string', $operation->parameters[2]->schema->type);
        self::assertSame(false, $operation->parameters[2]->required);
        self::assertSame(true, $operation->parameters[2]->schema->nullable);

        self::assertSame('nameWithExample', $operation->parameters[3]->name);
        self::assertSame('string', $operation->parameters[3]->schema->type);
        self::assertSame(true, $operation->parameters[3]->required);
        self::assertSame(DTO::EXAMPLE_NAME, $operation->parameters[3]->schema->example);
        self::assertSame(DTO::EXAMPLE_NAME, $operation->parameters[3]->example);

        self::assertSame('nameWithDescription', $operation->parameters[4]->name);
        self::assertSame('string', $operation->parameters[4]->schema->type);
        self::assertSame(true, $operation->parameters[4]->required);
        self::assertSame(DTO::DESCRIPTION, $operation->parameters[4]->schema->description);
        self::assertSame(DTO::DESCRIPTION, $operation->parameters[4]->description);
    }
}
