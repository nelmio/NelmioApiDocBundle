<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Controller;

use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Article81;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DeprecationController
{
    #[Route(path: '/legacy/null_options', name: 'legacy_null_options', methods: 'POST')]
    #[OA\Response(
        response: 200,
        description: 'Legacy null options',
        content: new Model(type: Article81::class, options: null),
    )]
    public function __invoke(Article81 $requestDTO): JsonResponse
    {
    }
}
