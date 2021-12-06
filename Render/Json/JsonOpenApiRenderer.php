<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Render\Json;

use Nelmio\ApiDocBundle\Render\OpenApiRendererInterface;
use Nelmio\ApiDocBundle\Render\RenderOpenApi;
use OpenApi\Annotations\OpenApi;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
class JsonOpenApiRenderer implements OpenApiRendererInterface
{
    public static function getFormat(): string
    {
        return RenderOpenApi::JSON;
    }

    public function __invoke(OpenApi $spec, array $options = []): Response
    {
        $options += [
            'no-pretty' => false,
        ];
        $flags = $options['no-pretty'] ? 0 : JSON_PRETTY_PRINT;

        return JsonResponse::fromJsonString(\json_encode($spec, $flags));
    }
}
