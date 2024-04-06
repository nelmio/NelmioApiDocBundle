<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\ModelDescriber;

use Doctrine\Common\Annotations\Reader;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\ModelDescriber\FormModelDescriber;
use OpenApi\Annotations\Property;
use OpenApi\Attributes\OpenApi;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyInfo\Type;

class FormModelDescriberTest extends TestCase
{
    /**
     * @dataProvider provideCsrfProtectionOptions
     */
    public function testDescribeCreatesTokenPropertyDependingOnOptions(bool $csrfProtectionEnabled, string $tokenName, bool $expectProperty): void
    {
        $formConfigMock = $this->createMock(FormConfigInterface::class);
        $formConfigMock->expects(self::exactly($csrfProtectionEnabled ? 2 : 1))
            ->method('getOption')
            ->willReturnCallback(function (string $option, $default) use ($csrfProtectionEnabled, $tokenName) {
                if ('csrf_protection' === $option) {
                    return $csrfProtectionEnabled;
                }

                if ('csrf_field_name' === $option) {
                    return $tokenName;
                }

                return $default;
            });

        $formMock = $this->createMock(FormInterface::class);
        $formMock->expects(self::exactly($csrfProtectionEnabled ? 2 : 1))
            ->method('getConfig')
            ->willReturn($formConfigMock);

        $formFactoryMock = $this->createMock(FormFactoryInterface::class);
        $formFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($formMock);

        $annotationReader = $this->createMock(Reader::class);

        $api = new OpenApi();
        $model = new Model(new Type(Type::BUILTIN_TYPE_OBJECT, false, FormType::class));
        $schema = $this->initSchema();
        $modelRegistry = new ModelRegistry([], $api);

        $describer = new FormModelDescriber($formFactoryMock, $annotationReader, [], false, true);
        $describer->setModelRegistry($modelRegistry);

        $describer->describe($model, $schema);

        if ($expectProperty) {
            $filteredProperties = array_filter($schema->properties, function (Property $property) use ($tokenName) {
                return $property->property === $tokenName;
            });

            self::assertCount(1, $filteredProperties);
        } else {
            self::assertSame(Generator::UNDEFINED, $schema->properties);
        }
    }

    public static function provideCsrfProtectionOptions(): \Generator
    {
        yield [true, '_token', true];
        yield [true, '_another_token', true];
        yield [false, '_token', false];
    }

    private function initSchema(): \OpenApi\Annotations\Schema
    {
        if (PHP_VERSION_ID < 80000) {
            return new \OpenApi\Annotations\Schema([]);
        }

        return new \OpenApi\Attributes\Schema(); // union types, used in schema attribute require PHP >= 8.0.0
    }
}
