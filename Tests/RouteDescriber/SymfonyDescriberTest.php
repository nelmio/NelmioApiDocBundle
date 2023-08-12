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
use Nelmio\ApiDocBundle\RouteDescriber\SymfonyAnnotationDescriber\SymfonyAnnotationDescriber;
use Nelmio\ApiDocBundle\RouteDescriber\SymfonyDescriber;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Operation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionParameter;
use Symfony\Component\Routing\Route;
use const PHP_VERSION_ID;

class SymfonyDescriberTest extends TestCase
{
    /**
     * @var MockObject&SymfonyAnnotationDescriber
     */
    private $symfonyAnnotationDescriberMock;

    /**
     * @var SymfonyDescriber
     */
    private $symfonyDescriber;

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

        $this->symfonyAnnotationDescriberMock = $this->createMock(SymfonyAnnotationDescriber::class);

        $this->modelRegistry = new ModelRegistry(
            [],
            $this->openApi = new OpenApi([]),
            []
        );

        $this->symfonyDescriber = new SymfonyDescriber(
            [$this->symfonyAnnotationDescriberMock]
        );

        $this->symfonyDescriber->setModelRegistry($this->modelRegistry);
    }

    public function testDescribe(): void
    {
        $reflectionParameter = $this->createStub(ReflectionParameter::class);

        $reflectionMethodStub = $this->createStub(ReflectionMethod::class);
        $reflectionMethodStub->method('getParameters')->willReturn([$reflectionParameter]);

        $this->symfonyAnnotationDescriberMock
            ->expects(self::exactly(count(Util::OPERATIONS)))
            ->method('supports')
            ->with($reflectionParameter)
            ->willReturn(true)
        ;

        $this->symfonyAnnotationDescriberMock
            ->expects(self::exactly(count(Util::OPERATIONS)))
            ->method('describe')
            ->with(
                $this->openApi,
                self::isInstanceOf(Operation::class),
                $reflectionParameter
            )
        ;

        $this->symfonyDescriber->describe(
            $this->openApi,
            new Route('/'),
            $reflectionMethodStub
        );
    }

    public function testDescribeSkipsUnsupportedDescribers(): void
    {
        $reflectionParameter = $this->createStub(ReflectionParameter::class);

        $reflectionMethodStub = $this->createStub(ReflectionMethod::class);
        $reflectionMethodStub->method('getParameters')->willReturn([$reflectionParameter]);

        $this->symfonyAnnotationDescriberMock
            ->expects(self::exactly(count(Util::OPERATIONS)))
            ->method('supports')
            ->with($reflectionParameter)
            ->willReturn(false)
        ;

        $this->symfonyAnnotationDescriberMock
            ->expects(self::never())
            ->method('describe')
        ;

        $this->symfonyDescriber->describe(
            $this->openApi,
            new Route('/'),
            $reflectionMethodStub
        );
    }
}
