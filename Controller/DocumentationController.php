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

use Nelmio\ApiDocBundle\ApiDocGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class DocumentationController
{
    private $apiDocGenerator;

    public function __construct(ApiDocGenerator $apiDocGenerator)
    {
        $this->apiDocGenerator = $apiDocGenerator;
    }

    public function __invoke(Request $request)
    {
        $spec = $this->apiDocGenerator->generate()->toArray();
        if ('' !== $request->getBaseUrl()) {
            $spec['basePath'] = $request->getBaseUrl();
        }

        return new JsonResponse($spec);
    }
}
