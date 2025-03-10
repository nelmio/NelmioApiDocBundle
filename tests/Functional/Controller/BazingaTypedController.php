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
use Nelmio\ApiDocBundle\Tests\Functional\EntityExcluded\BazingaUserTyped;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;

#[Route(host: 'api.example.com')]
class BazingaTypedController
{
    #[Route(path: '/api/bazinga_typed', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new Model(type: BazingaUserTyped::class)
    )]
    public function userTypedAction()
    {
    }
}
