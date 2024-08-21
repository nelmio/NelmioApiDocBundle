<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Controller;

use Nelmio\ApiDocBundle\Exception\RenderInvalidArgumentException;
use Nelmio\ApiDocBundle\Render\RenderOpenApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class SwaggerUiController
{
    private RenderOpenApi $renderOpenApi;

    private string $uiRenderer;

    public function __construct(RenderOpenApi $renderOpenApi, string $uiRenderer)
    {
        $this->renderOpenApi = $renderOpenApi;
        $this->uiRenderer = $uiRenderer;
    }

    public function __invoke(Request $request, string $area = 'default'): Response
    {
        try {
            $response = new Response(
                $this->renderOpenApi->renderFromRequest($request, RenderOpenApi::HTML, $area, [
                    'ui_renderer' => $this->uiRenderer,
                ]),
                Response::HTTP_OK,
                ['Content-Type' => 'text/html']
            );

            return $response->setCharset('UTF-8');
        } catch (RenderInvalidArgumentException $e) {
            $advice = '';
            if (false !== strpos($area, '.json')) {
                $advice = ' Since the area provided contains `.json`, the issue is likely caused by route priorities. Try switching the Swagger UI / the json documentation routes order.';
            }

            throw new BadRequestHttpException(sprintf('Area "%s" is not supported as it isn\'t defined in config.%s', $area, $advice), $e);
        }
    }
}
