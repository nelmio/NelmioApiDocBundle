<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\RouteDescriber\SymfonyAnnotationDescriber;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\ModelDescriber\SelfDescribingModelDescriber;
use Nelmio\ApiDocBundle\RouteDescriber\InlineParameterDescriber\SymfonyMapQueryStringDescriber;
use Nelmio\ApiDocBundle\Tests\Functional\WebTestCase;
use Nelmio\ApiDocBundle\Tests\RouteDescriber\Fixtures\DTO;
use Nelmio\ApiDocBundle\Tests\RouteDescriber\Fixtures\SymfonyDescriberMapQueryStringClass;
use OpenApi\Annotations\OpenApi;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use const PHP_VERSION_ID;

class SymfonyMapQueryStringDescriberTest extends WebTestCase
{
    /**
     * @var OpenApi
     */
    private $openApi;
    /**
     * @var SymfonyMapQueryStringDescriber
     */
    private $symfonyMapQueryStringDescriber;

    /**
     * @var ArgumentMetadataFactoryInterface
     */
    private $argumentMetadataFactory;

    protected function setUp(): void
    {
        if (PHP_VERSION_ID < 80100) {
            self::markTestSkipped('Attributes require PHP 8');
        }

        if (!class_exists(MapQueryString::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryString attribute not found');
        }

        $this->argumentMetadataFactory = self::getContainer()->get('argument_metadata_factory');

        $this->openApi = new OpenApi([]);

        $this->symfonyMapQueryStringDescriber = new SymfonyMapQueryStringDescriber([new SelfDescribingModelDescriber()]);

        $registry = new ModelRegistry([], $this->openApi, []);

        $this->symfonyMapQueryStringDescriber->setModelRegistry($registry);
    }

    /**
     * @dataProvider provideMapQueryStringTestData
     */
    public function testMapQueryString(callable $function, bool $required): void
    {
        $argumentMetaData = $this->argumentMetadataFactory->createArgumentMetadata($function)[0];

        $this->symfonyMapQueryStringDescriber->describe(
            $this->openApi,
            $operation = new Operation([]),
            $argumentMetaData
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
            self::assertSame($required, $queryParameter->required);
        }
    }

    public static function provideMapQueryStringTestData(): iterable
    {
        yield 'it documents query string parameters' => [
            function (
                #[MapQueryString] SymfonyDescriberMapQueryStringClass $parameter1,
            ) {
            },
            true,
        ];

        yield 'it documents a nullable type as optional' => [
            function (
                #[MapQueryString] ?SymfonyDescriberMapQueryStringClass $parameter1,
            ) {
            },
            false,
        ];

        yield 'it documents a default value as optional' => [
            function (
                #[MapQueryString] ?SymfonyDescriberMapQueryStringClass $parameter1,
            ) {
            },
            false,
        ];
    }

    public function testItDescribesProperties(): void
    {
        $function = function (
            #[MapQueryString] DTO $DTO,
        ) {
        };

        $argumentMetaData = $this->argumentMetadataFactory->createArgumentMetadata($function)[0];

        $this->symfonyMapQueryStringDescriber->describe(
            $this->openApi,
            $operation = new Operation([]),
            $argumentMetaData
        );

        // Test it registers the model
        $modelSchema = $this->openApi->components->schemas[0];

        self::assertEquals('object', $modelSchema->type);
        self::assertEquals(DTO::getRequired(), $modelSchema->required);
        self::assertEquals(DTO::getProperties(), $modelSchema->properties);

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
