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

use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\RouteDescriber\InlineParameterDescriber;
use Nelmio\ApiDocBundle\RouteDescriber\InlineParameterDescriber\InlineParameterDescriberInterface;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Operation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use Symfony\Component\Routing\Route;
use const PHP_VERSION_ID;

class SymfonyDescriberTest extends TestCase
{
    /**
     * @var MockObject&ArgumentMetadataFactoryInterface
     */
    private $argumentMetadataFactoryInterface;

    /**
     * @var MockObject&InlineParameterDescriberInterface
     */
    private $inlineParameterDescriberInterfaceMock;

    /**
     * @var InlineParameterDescriber
     */
    private $inlineParameterDescriber;

    /**
     * @var ModelRegistry
     */
    private $modelRegistry;
    /**
     * @var OpenApi
     */
    private $openApi;

    protected function setUp(): void
    {
        if (PHP_VERSION_ID < 80100) {
            self::markTestSkipped('Attributes require PHP 8');
        }

        $this->argumentMetadataFactoryInterface = $this->createMock(ArgumentMetadataFactoryInterface::class);
        $this->inlineParameterDescriberInterfaceMock = $this->createMock(InlineParameterDescriberInterface::class);

        $this->modelRegistry = new ModelRegistry(
            [],
            $this->openApi = new OpenApi([]),
            []
        );

        $this->inlineParameterDescriber = new InlineParameterDescriber(
            $this->argumentMetadataFactoryInterface,
            [$this->inlineParameterDescriberInterfaceMock]
        );

        $this->inlineParameterDescriber->setModelRegistry($this->modelRegistry);
    }

    public function testDescribe(): void
    {
        $argumentMetaData = $this->createStub(ArgumentMetadata::class);

        $reflectionMethodStub = $this->createStub(ReflectionMethod::class);

        $this->argumentMetadataFactoryInterface
            ->expects(self::once())
            ->method('createArgumentMetadata')
            ->with('foo', $reflectionMethodStub)
            ->willReturn([$argumentMetaData])
        ;

        $this->inlineParameterDescriberInterfaceMock
            ->expects(self::exactly(count(Util::OPERATIONS)))
            ->method('supports')
            ->with($argumentMetaData)
            ->willReturn(true)
        ;

        $this->inlineParameterDescriberInterfaceMock
            ->expects(self::exactly(count(Util::OPERATIONS)))
            ->method('describe')
            ->with(
                $this->openApi,
                self::isInstanceOf(Operation::class),
                $argumentMetaData
            )
        ;

        $this->inlineParameterDescriber->describe(
            $this->openApi,
            new Route('/', ['_controller' => 'foo']),
            $reflectionMethodStub
        );
    }

    public function testDescribeSkipsUnsupportedDescribers(): void
    {
        $argumentMetaData = $this->createStub(ArgumentMetadata::class);

        $reflectionMethodStub = $this->createStub(ReflectionMethod::class);

        $this->argumentMetadataFactoryInterface
            ->expects(self::once())
            ->method('createArgumentMetadata')
            ->with('foo', $reflectionMethodStub)
            ->willReturn([$argumentMetaData])
        ;

        $this->inlineParameterDescriberInterfaceMock
            ->expects(self::exactly(count(Util::OPERATIONS)))
            ->method('supports')
            ->with($argumentMetaData)
            ->willReturn(false)
        ;

        $this->inlineParameterDescriberInterfaceMock
            ->expects(self::never())
            ->method('describe')
        ;

        $this->inlineParameterDescriber->describe(
            $this->openApi,
            new Route('/', ['_controller' => 'foo']),
            $reflectionMethodStub
        );
    }
}
