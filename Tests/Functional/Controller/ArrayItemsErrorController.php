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

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\ArrayItemsError\Foo;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAT;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Annotation\Route;

if (Kernel::MAJOR_VERSION < 7) {
    /**
     * @Route(host="api.example.com")
     */
    class ArrayItemsErrorController
    {
        /**
         * @Route("/api/error", methods={"GET"})
         *
         * @OA\Response(
         *     response=200,
         *     description="Success",
         *
         *     @Model(type=Foo::class)
         * )
         */
        public function errorAction()
        {
        }
    }
} else {
    #[Route(host: 'api.example.com')]
    class ArrayItemsErrorController
    {
        #[Route('/api/error', methods: ['GET'])]
        #[OAT\Response(response: 200, description: 'Success', content: new Model(type: Foo::class))]
        public function errorAction()
        {
        }
    }
}
