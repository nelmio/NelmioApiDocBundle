<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\RouteDescriber\SymfonyAnnotationDescriber;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\ModelDescriber\SelfDescribingModelDescriber;
use Nelmio\ApiDocBundle\RouteDescriber\SymfonyAnnotationDescriber\SymfonyMapQueryStringDescriber;
use Nelmio\ApiDocBundle\Tests\RouteDescriber\Fixtures\SymfonyDescriberMapQueryStringClass;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use const PHP_VERSION_ID;

class SymfonyMapQueryStringDescriberTest extends TestCase
{
    private OpenApi $openApi;
    private SymfonyMapQueryStringDescriber $symfonyMapQueryStringDescriber;

    protected function setUp(): void
    {
        if (PHP_VERSION_ID < 80100) {
            self::markTestSkipped('Attributes require PHP 8');
        }

        if (!class_exists(MapQueryString::class)) {
            self::markTestSkipped('Symfony 6.3 MapQueryString attribute not found');
        }

        $this->openApi = new OpenApi([]);

        $this->symfonyMapQueryStringDescriber = new SymfonyMapQueryStringDescriber();

        $registry = new ModelRegistry([new SelfDescribingModelDescriber()], $this->openApi, []);

        $this->symfonyMapQueryStringDescriber->setModelRegistry($registry);
    }

    /**
     * @dataProvider provideMapQueryStringTestData
     */
    public function testMapQueryString(callable $function): void
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
            $isQueryOptional = (Generator::UNDEFINED !== $expectedModelProperty->nullable && $expectedModelProperty->nullable)
                || Generator::UNDEFINED !== $expectedModelProperty->default;

            self::assertSame($isQueryOptional, $queryParameter->allowEmptyValue);
            self::assertSame(!$isQueryOptional, $queryParameter->required);
        }
    }

    public static function provideMapQueryStringTestData(): iterable
    {
        yield 'it documents query string parameters' => [
            function (
                #[MapQueryString] SymfonyDescriberMapQueryStringClass $parameter1,
            ) {
            },
        ];
    }
}
