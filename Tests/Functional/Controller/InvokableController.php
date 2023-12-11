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

use OpenApi\Annotations as OA;
use OpenApi\Attributes\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Annotation\Route;

if (Kernel::MAJOR_VERSION < 7) {
    /**
     * Prevents a regression (see https://github.com/nelmio/NelmioApiDocBundle/issues/1559).
     *
     * @Route("/api/invoke", host="api.example.com", name="invokable", methods={"GET"})
     *
     * @OA\Response(
     *    response=200,
     *    description="Invokable!"
     * )
     */
    class InvokableController
    {
        public function __invoke()
        {
        }
    }
} else {
    /**
     * Prevents a regression (see https://github.com/nelmio/NelmioApiDocBundle/issues/1559).
     */
    #[Response(response: 200, description: 'Invokable!')]
    #[Route('/api/invoke', host: 'api.example.com', name: 'invokable', methods: ['GET'])]
    class InvokableController
    {
        public function __invoke()
        {
        }
    }
}
