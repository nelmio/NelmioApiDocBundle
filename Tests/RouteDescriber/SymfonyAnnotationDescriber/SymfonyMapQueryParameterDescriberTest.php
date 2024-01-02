<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\RouteDescriber\SymfonyAnnotationDescriber;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\RouteDescriber\InlineParameterDescriber\SymfonyMapQueryParameterDescriber;
use Nelmio\ApiDocBundle\Tests\Functional\WebTestCase;
use OpenApi\Annotations\OpenApi;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use const PHP_VERSION_ID;

class SymfonyMapQueryParameterDescriberTest extends WebTestCase
{
    /**
     * @var SymfonyMapQueryParameterDescriber
     */
    private $symfonyMapQueryParameterDescriber;

    /**
     * @var ArgumentMetadataFactoryInterface
     */
    private $argumentMetadataFactory;

    protected function setUp(): void
    {
        if (PHP_VERSION_ID < 80100) {
            self::markTestSkipped('Attributes require PHP 8');
        }

        if (!class_exists(MapQueryParameter::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryParameter attribute not found');
        }

        $this->argumentMetadataFactory = self::getContainer()->get('argument_metadata_factory');

        $this->symfonyMapQueryParameterDescriber = new SymfonyMapQueryParameterDescriber();
    }

    /**
     * @dataProvider provideMapQueryParameterTestData
     */
    public function testMapQueryParameter(callable $function): void
    {
        $argumentMetaData = $this->argumentMetadataFactory->createArgumentMetadata($function)[0];

        $this->symfonyMapQueryParameterDescriber->describe(
            new OpenApi([]),
            $operation = new Operation([]),
            $argumentMetaData
        );

        /** @var MapQueryParameter $mapQueryParameter */
        $mapQueryParameter = $argumentMetaData->getAttributes(MapQueryParameter::class, ArgumentMetadata::IS_INSTANCEOF)[0];

        $documentationParameter = $operation->parameters[0];
        self::assertSame($mapQueryParameter->name ?? $argumentMetaData->getName(), $documentationParameter->name);
        self::assertSame('query', $documentationParameter->in);
        self::assertSame(!$argumentMetaData->hasDefaultValue() && !$argumentMetaData->isNullable(), $documentationParameter->required);

        $schema = $documentationParameter->schema;
        self::assertSame('integer', $schema->type);
        if ($argumentMetaData->hasDefaultValue()) {
            self::assertSame($argumentMetaData->getDefaultValue(), $schema->default);
        }

        if (FILTER_VALIDATE_REGEXP === $mapQueryParameter->filter) {
            self::assertSame($mapQueryParameter->options['regexp'], $schema->pattern);
        }
    }

    public static function provideMapQueryParameterTestData(): iterable
    {
        yield 'it documents query parameters' => [
            function (
                #[MapQueryParameter] int $parameter1,
            ) {
            },
        ];

        yield 'it documents query parameters with default values' => [
            function (
                #[MapQueryParameter] int $parameter1 = 123,
            ) {
            },
        ];

        yield 'it documents query parameters with nullable types' => [
            function (
                #[MapQueryParameter] ?int $parameter1,
            ) {
            },
        ];

        yield 'it uses MapQueryParameter name argument as name' => [
            function (
                #[MapQueryParameter('someOtherParameter1Name')] int $parameter1,
            ) {
            },
        ];

        yield 'it documents regex pattern' => [
            function (
                #[MapQueryParameter(filter: FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^\d+$/'])] int $parameter1,
            ) {
            },
        ];
    }
}
