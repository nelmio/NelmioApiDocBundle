<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Controller\Spec;

use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as Classic;
use OpenApi\Spec as OA;
use Symfony\Component\Routing\Attribute\Route;

/**
 * {@see \Nelmio\ApiDocBundle\Tests\Functional\Controller\RouteVariantsController} with spec
 * attributes. `mixed()` keeps a classic response next to a spec one: both are read, and they merge
 * into one operation.
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
        #[OA\Parameter\Path(description: 'The product')]
        string $product_id,
    ): void {
    }

    #[Route('/mixed', name: 'variants_mixed', methods: ['GET', 'POST'])]
    #[OA\Response(response: 200, description: 'Mixed')]
    #[Classic\Response(response: 404, description: 'Not found')]
    #[Security(name: 'api_key')]
    public function mixed(): void
    {
    }
}
