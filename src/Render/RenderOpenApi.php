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

use Nelmio\ApiDocBundle\Exception\RenderInvalidArgumentException;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Server;
use OpenApi\Context;
use OpenApi\Generator;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class RenderOpenApi
{
    public const HTML = 'html';
    public const JSON = 'json';
    public const YAML = 'yaml';

    private ContainerInterface $generatorLocator;

    /** @var array<string, OpenApiRenderer|null> */
    private array $openApiRenderers = [];

    public function __construct(ContainerInterface $generatorLocator, ?OpenApiRenderer ...$openApiRenderers)
    {
        $this->generatorLocator = $generatorLocator;
        foreach ($openApiRenderers as $openApiRenderer) {
            if (null === $openApiRenderer) {
                continue;
            }

            $this->openApiRenderers[$openApiRenderer->getFormat()] = $openApiRenderer;
        }
    }

    /**
     * @return string[]
     */
    public function getAvailableFormats(): array
    {
        return array_keys($this->openApiRenderers);
    }

    /**
     * @param array<string, mixed> $extraOptions
     *
     * @return string
     */
    public function renderFromRequest(Request $request, string $format, string $area, array $extraOptions = [])
    {
        $options = [];
        if ('' !== $request->getBaseUrl()) {
            $options += [
                'fallback_url' => $request->getSchemeAndHttpHost().$request->getBaseUrl(),
            ];
        }
        $options += $extraOptions;

        return $this->render($format, $area, $options);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws \InvalidArgumentException If the area to dump is not valid
     */
    public function render(string $format, string $area, array $options = []): string
    {
        if (!$this->generatorLocator->has($area)) {
            throw new RenderInvalidArgumentException(\sprintf('Area "%s" is not supported.', $area));
        } elseif (!\array_key_exists($format, $this->openApiRenderers)) {
            throw new RenderInvalidArgumentException(\sprintf('Format "%s" is not supported.', $format));
        }

        /** @var OpenApi $spec */
        $spec = $this->generatorLocator->get($area)->generate();
        $tmpServers = $spec->servers;
        try {
            $spec->servers = $this->getServersFromOptions($spec, $options);

            return $this->openApiRenderers[$format]->render($spec, $options);
        } finally {
            $spec->servers = $tmpServers; // Restore original value as we should not modify OpenApi object from the generator
        }
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return Server[]|Generator::UNDEFINED
     */
    private function getServersFromOptions(OpenApi $spec, array $options)
    {
        if (\array_key_exists('server_url', $options)) {
            return [new Server(['url' => $options['server_url'], '_context' => new Context()])];
        }

        if (Generator::UNDEFINED !== $spec->servers) {
            return $spec->servers;
        }

        if (\array_key_exists('fallback_url', $options)) {
            return [new Server(['url' => $options['fallback_url'], '_context' => new Context()])];
        }

        return Generator::UNDEFINED;
    }
}
