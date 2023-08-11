<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\RouteDescriber\SymfonyAnnotationDescriber;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\RouteDescriber\SymfonyAnnotationDescriber\SymfonyMapQueryParameterDescriber;
use OpenApi\Annotations\OpenApi;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use const PHP_VERSION_ID;

class SymfonyMapQueryParameterDescriberTest extends TestCase
{
    private SymfonyMapQueryParameterDescriber $symfonyMapQueryParameterDescriber;

    protected function setUp(): void
    {
        if (PHP_VERSION_ID < 80100) {
            self::markTestSkipped('Attributes require PHP 8');
        }

        if (!class_exists(MapQueryParameter::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryParameter attribute not found');
        }

        $this->symfonyMapQueryParameterDescriber = new SymfonyMapQueryParameterDescriber();
    }

    /**
     * @dataProvider provideMapQueryParameterTestData
     */
    public function testMapQueryParameter(callable $function): void
    {
        $parameter = new ReflectionParameter($function, 'parameter1');

        $this->symfonyMapQueryParameterDescriber->describe(
            new OpenApi([]),
            $operation = new Operation([]),
            $parameter
        );

        /** @var MapQueryParameter $mapQueryParameter */
        $mapQueryParameter = $parameter->getAttributes(MapQueryParameter::class, ReflectionAttribute::IS_INSTANCEOF)[0]->newInstance();

        $documentationParameter = $operation->parameters[0];
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
