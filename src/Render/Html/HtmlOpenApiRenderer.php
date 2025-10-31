<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Render\Html;

use Nelmio\ApiDocBundle\Render\OpenApiRenderer;
use Nelmio\ApiDocBundle\Render\RenderOpenApi;
use OpenApi\Annotations\OpenApi;
use Twig\Environment;

/**
 * @internal
 */
class HtmlOpenApiRenderer implements OpenApiRenderer
{
    /**
     * @param Environment|\Twig_Environment $twig       support for Twig 2 was removed with Symfony 7 and this `\Twig_Environment` can be removed when dropping support for Symfony 6
     * @param array<string, mixed>          $htmlConfig
     */
    public function __construct(
        private Environment|\Twig_Environment $twig,
        private array $htmlConfig,
    ) {
    }

    public function getFormat(): string
    {
        return RenderOpenApi::HTML;
    }

    public function render(OpenApi $spec, array $options = []): string
    {
        $options += $this->htmlConfig;

        if (isset($options['ui_renderer']) && Renderer::REDOCLY === $options['ui_renderer']) {
            return $this->twig->render(
                '@NelmioApiDoc/Redocly/index.html.twig',
                [
                    'swagger_data' => ['spec' => json_decode($spec->toJson(), true)],
                    'assets_mode' => $options['assets_mode'],
                    'redocly_config' => $options['redocly_config'],
                ]
            );
        }

        if (isset($options['ui_renderer']) && Renderer::STOPLIGHT === $options['ui_renderer']) {
            return $this->twig->render(
                '@NelmioApiDoc/Stoplight/index.html.twig',
                [
                    'swagger_data' => ['spec' => json_decode($spec->toJson(), true)],
                    'assets_mode' => $options['assets_mode'],
                    'stoplight_config' => $options['stoplight_config'],
                ]
            );
        }

        return $this->twig->render(
            '@NelmioApiDoc/SwaggerUi/index.html.twig',
            [
                'swagger_data' => ['spec' => json_decode($spec->toJson(), true)],
                'assets_mode' => $options['assets_mode'],
                'swagger_ui_config' => $options['swagger_ui_config'],
            ]
        );
    }
}
