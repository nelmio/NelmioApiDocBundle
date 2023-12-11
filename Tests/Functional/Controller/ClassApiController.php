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

use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use OpenApi\Attributes\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Annotation\Route;

if (Kernel::MAJOR_VERSION < 7) {
    /**
     * @Route("/api", host="api.example.com")
     * @Security(name="basic")
     */
    class ClassApiController
    {
        /**
         * @Route("/security/class")
         * @OA\Response(response="201", description="")
         */
        public function securityAction()
        {
        }
    }
} else {
    #[Security(name: 'basic')]
    #[Route("/api", host: "api.example.com")]
    class ClassApiController
    {
        #[Response(response: 201, description: '')]
        #[Route("/security/class")]
        public function securityAction()
        {
        }
    }
}