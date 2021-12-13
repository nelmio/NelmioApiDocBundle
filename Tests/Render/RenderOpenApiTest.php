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

use InvalidArgumentException;
use Nelmio\ApiDocBundle\Render\OpenApiRenderer;
use Nelmio\ApiDocBundle\Render\RenderOpenApi;
use OpenApi\Annotations\OpenApi;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class RenderOpenApiTest extends TestCase
{
    private $area = 'irrelevant area';
    private $format = 'irrelevant format';
    private $hasArea = true;

    public function testRender()
    {
        $openApiRenderer = $this->createMock(OpenApiRenderer::class);
        $openApiRenderer->method('getFormat')->willReturn($this->format);
        $openApiRenderer->expects($this->once())->method('render');
        $this->renderOpenApi($openApiRenderer);
    }

    public function testUnknownFormat()
    {
        $availableOpenApiRenderers = [];
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage(sprintf('Format "%s" is not supported.', $this->format));
        $this->renderOpenApi(...$availableOpenApiRenderers);
    }

    public function testUnknownArea()
    {
        $this->hasArea = false;
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage(sprintf('Area "%s" is not supported.', $this->area));
        $this->renderOpenApi();
    }

    public function testNullFormat()
    {
        $openApiRenderer = $this->createMock(OpenApiRenderer::class);
        $openApiRenderer->method('getFormat')->willReturn($this->format);
        $openApiRenderer->expects($this->once())->method('render');

        $availableOpenApiRenderers = [
            $openApiRenderer,
            null,
        ];
        $this->renderOpenApi(...$availableOpenApiRenderers);
    }

    private function renderOpenApi(...$openApiRenderer): void
    {
        $spec = $this->createMock(OpenApi::class);
        $generator = new class($spec) {
            public function __construct(private $spec)
            {
            }

            public function generate()
            {
                return $this->spec;
            }
        };

        $generatorLocator = $this->createMock(ContainerInterface::class);
        $generatorLocator->method('has')->willReturn($this->hasArea);
        $generatorLocator->method('get')->willReturn($generator);

        $renderOpenApi = new RenderOpenApi($generatorLocator, ...$openApiRenderer);
        $renderOpenApi->render($this->format, $this->area, []);
    }
}
