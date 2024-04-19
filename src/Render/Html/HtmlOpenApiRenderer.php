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
    /** @var Environment|\Twig_Environment */
    private $twig;
    /** @var array<string, mixed> */
    private array $htmlConfig;

    /**
     * @param Environment|\Twig_Environment $twig
     * @param array<string, mixed>          $htmlConfig
     */
    public function __construct($twig, array $htmlConfig)
    {
        if (!$twig instanceof \Twig_Environment && !$twig instanceof Environment) {
            throw new \InvalidArgumentException(sprintf('Providing an instance of "%s" as twig is not supported.', get_class($twig)));
        }
        $this->twig = $twig;
        $this->htmlConfig = $htmlConfig;
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
