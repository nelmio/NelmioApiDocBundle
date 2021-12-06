<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Render\Yaml;

use Nelmio\ApiDocBundle\Render\OpenApiRendererInterface;
use Nelmio\ApiDocBundle\Render\RenderOpenApi;
use OpenApi\Annotations\OpenApi;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
class YamlOpenApiRenderer implements OpenApiRendererInterface
{
    public static function getFormat(): string
    {
        return RenderOpenApi::YAML;
    }

    public function __invoke(OpenApi $spec, array $options = []): Response
    {
        $response = new Response(
            $spec->toYaml(),
            Response::HTTP_OK,
            ['Content-Type' => 'text/x-yaml']
        );

        return $response->setCharset('UTF-8');
    }
}
