<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Render;

use Nelmio\ApiDocBundle\Render\OpenApiRenderer;
use Nelmio\ApiDocBundle\Render\RenderOpenApi;
use OpenApi\Annotations\OpenApi;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class RenderOpenApiTest extends TestCase
{
    private const AREA = 'irrelevant area';
    private const FORMAT = 'irrelevant format';
    private bool $hasArea = true;

    public function testRender(): void
    {
        $openApiRenderer = $this->createMock(OpenApiRenderer::class);
        $openApiRenderer->method('getFormat')->willReturn(self::FORMAT);
        $openApiRenderer->expects(self::once())->method('render');
        $this->renderOpenApi($openApiRenderer);
    }

    public function testUnknownFormat(): void
    {
        $availableOpenApiRenderers = [];
        $this->expectExceptionObject(new \InvalidArgumentException(sprintf('Format "%s" is not supported.', self::FORMAT)));
        $this->renderOpenApi(...$availableOpenApiRenderers);
    }

    public function testUnknownArea(): void
    {
        $this->hasArea = false;
        $this->expectExceptionObject(new \InvalidArgumentException(sprintf('Area "%s" is not supported.', self::AREA)));
        $this->renderOpenApi();
    }

    public function testNullFormat(): void
    {
        $openApiRenderer = $this->createMock(OpenApiRenderer::class);
        $openApiRenderer->method('getFormat')->willReturn(self::FORMAT);
        $openApiRenderer->expects(self::once())->method('render');

        $availableOpenApiRenderers = [
            $openApiRenderer,
            null,
        ];
        $this->renderOpenApi(...$availableOpenApiRenderers);
    }

    private function renderOpenApi(?OpenApiRenderer ...$openApiRenderer): void
    {
        $spec = $this->createMock(OpenApi::class);
        $generator = new class($spec) {
            private OpenApi $spec;

            public function __construct(OpenApi $spec)
            {
                $this->spec = $spec;
            }

            public function generate(): OpenApi
            {
                return $this->spec;
            }
        };

        $generatorLocator = $this->createMock(ContainerInterface::class);
        $generatorLocator->method('has')->willReturn($this->hasArea);
        $generatorLocator->method('get')->willReturn($generator);

        $renderOpenApi = new RenderOpenApi($generatorLocator, ...$openApiRenderer);
        $renderOpenApi->render(self::FORMAT, self::AREA, []);
    }
}
