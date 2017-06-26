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

final class DocumentationController
{
    private $apiDocGenerator;

    public function __construct(ApiDocGenerator $apiDocGenerator)
    {
        $this->apiDocGenerator = $apiDocGenerator;
    }

    public function __invoke()
    {
        return new JsonResponse($this->apiDocGenerator->generate()->toArray());
    }
}
