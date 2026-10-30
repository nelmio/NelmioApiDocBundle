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

use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Route shapes, written with classic attributes; {@see Spec\RouteVariantsController} is its twin.
 */
#[Route('/variants')]
class RouteVariantsController
{
    #[Route('/one', name: 'variants_one', methods: 'GET')]
    #[Route('/two', name: 'variants_two', methods: 'GET')]
    #[OA\Response(response: 200, description: 'Several routes')]
    public function severalRoutes(): void
    {
    }

    #[Route('/products/{product_id}', name: 'variants_product', methods: 'GET')]
    #[OA\Response(response: 200, description: 'A product')]
    public function parameterAttribute(
        #[OA\PathParameter(description: 'The product')]
        string $product_id,
    ): void {
    }

    #[Route('/mixed', name: 'variants_mixed', methods: ['GET', 'POST'])]
    #[OA\Response(response: 200, description: 'Mixed')]
    #[OA\Response(response: 404, description: 'Not found')]
    #[Security(name: 'api_key')]
    public function mixed(): void
    {
    }
}
