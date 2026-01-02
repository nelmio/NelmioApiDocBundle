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
use Nelmio\ApiDocBundle\Tests\Functional\Entity\BazingaUser;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Attribute\Route;

#[Route(host: 'api.example.com')]
class BazingaController
{
    #[Route(path: '/api/bazinga', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new Model(type: BazingaUser::class)
    )]
    public function userAction(): void
    {
    }

    #[Route(path: '/api/bazinga_foo', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new Model(type: BazingaUser::class, groups: ['foo'])
    )]
    public function userGroupAction(): void
    {
    }
}
