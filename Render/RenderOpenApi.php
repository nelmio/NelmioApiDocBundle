<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Render;

use InvalidArgumentException;
use OpenApi\Annotations\OpenApi;
use Psr\Container\ContainerInterface;

class RenderOpenApi
{
    public const HTML = 'html';
    public const JSON = 'json';
    public const YAML = 'yaml';

    /** @var ContainerInterface */
    private $generatorLocator;

    /** @var array<string, OpenApiRenderer> */
    private $openApiRenderers = [];

    public function __construct(ContainerInterface $generatorLocator, OpenApiRenderer ...$openApiRenderers)
    {
        $this->generatorLocator = $generatorLocator;
        foreach ($openApiRenderers as $openApiRenderer) {
            $this->openApiRenderers[$openApiRenderer->getFormat()] = $openApiRenderer;
        }
    }

    public function getAvailableFormats(): array
    {
        return array_keys($this->openApiRenderers);
    }

    /**
     * @throws InvalidArgumentException If the area to dump is not valid
     */
    public function render(string $format, string $area, array $options = []): string
    {
        if (!$this->generatorLocator->has($area)) {
            throw new InvalidArgumentException(sprintf('Area "%s" is not supported.', $area));
        } elseif (!array_key_exists($format, $this->openApiRenderers)) {
            throw new InvalidArgumentException(sprintf('Format "%s" is not supported.', $format));
        }

        /** @var OpenApi $spec */
        $spec = $this->generatorLocator->get($area)->generate();

        return $this->openApiRenderers[$format]->render($spec, $options);
    }
}
