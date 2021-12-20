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
use Nelmio\ApiDocBundle\Tests\Functional\EntityExcluded\BazingaUserTyped;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

if (\PHP_VERSION_ID >= 80100) {
    #[Route(host: 'api.example.com')]
    class BazingaTypedController
    {
        #[Route('/api/bazinga_typed', methods: ['GET'])]
        #[OA\Response(
            response: 200,
            description: 'Success',
            properties: ['value' => new Model(type: BazingaUserTyped::class)],
        )]
        public function userTypedAction()
        {
        }
    }
} else {
    /**
     * @Route(host="api.example.com")
     */
    class BazingaTypedController
    {
        /**
         * @Route("/api/bazinga_typed", methods={"GET"})
         * @OA\Response(
         *     response=200,
         *     description="Success",
         *     @Model(type=BazingaUserTyped::class)
         * )
         */
        public function userTypedAction()
        {
        }
    }
}
