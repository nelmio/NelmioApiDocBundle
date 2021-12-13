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
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security(name="basic")
 */
#[Route(path: '/api', host: 'api.example.com')]
class ClassApiController
{
    /**
     * @OA\Response(response="201", description="")
     */
    #[Route(path: '/security/class')]
    public function securityAction()
    {
    }
}
