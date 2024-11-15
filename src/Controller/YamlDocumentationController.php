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

use Nelmio\ApiDocBundle\Render\RenderOpenApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class YamlDocumentationController
{
    private RenderOpenApi $renderOpenApi;

    public function __construct(RenderOpenApi $renderOpenApi)
    {
        $this->renderOpenApi = $renderOpenApi;
    }

    public function __invoke(Request $request, string $area = 'default'): Response
    {
        try {
            $response = new Response(
                $this->renderOpenApi->renderFromRequest($request, RenderOpenApi::YAML, $area),
                Response::HTTP_OK,
                ['Content-Type' => 'text/x-yaml']
            );

            return $response->setCharset('UTF-8');
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException(\sprintf('Area "%s" is not supported as it isn\'t defined in config.', $area));
        }
    }
}
