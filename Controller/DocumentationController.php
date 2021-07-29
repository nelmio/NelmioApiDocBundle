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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class DocumentationController
{
    /**
     * @var RenderOpenApi
     */
    private $renderOpenApi;

    public function __construct(RenderOpenApi $renderOpenApi)
    {
        $this->renderOpenApi = $renderOpenApi;
    }

    public function __invoke(Request $request, $area = 'default')
    {
        try {
            return JsonResponse::fromJsonString(
                $this->renderOpenApi->renderFromRequest($request, RenderOpenApi::JSON, $area)
            );
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException(sprintf('Area "%s" is not supported as it isn\'t defined in config.', $area));
        }
    }
}
