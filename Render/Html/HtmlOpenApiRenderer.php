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

use InvalidArgumentException;
use Nelmio\ApiDocBundle\Render\OpenApiRendererInterface;
use Nelmio\ApiDocBundle\Render\RenderOpenApi;
use OpenApi\Annotations\OpenApi;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * @internal
 */
class HtmlOpenApiRenderer implements OpenApiRendererInterface
{
    /** @var Environment|\Twig_Environment */
    private $twig;

    public function __construct($twig)
    {
        if (!$twig instanceof \Twig_Environment && !$twig instanceof Environment) {
            throw new InvalidArgumentException(sprintf('Providing an instance of "%s" as twig is not supported.', get_class($twig)));
        }
        $this->twig = $twig;
    }

    public static function getFormat(): string
    {
        return RenderOpenApi::HTML;
    }

    public function __invoke(OpenApi $spec, array $options = []): Response
    {
        $options += [
            'assets_mode' => AssetsMode::CDN,
            'swagger_ui_config' => [],
        ];

        return new Response($this->twig->render(
            '@NelmioApiDoc/SwaggerUi/index.html.twig',
            [
                'swagger_data' => ['spec' => json_decode($spec->toJson(), true)],
                'assets_mode' => $options['assets_mode'],
                'swagger_ui_config' => $options['swagger_ui_config'],
            ]
        ));
    }
}
