<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\ControllerPhp81;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\BazingaUser;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

#[Route(host: 'api.example.com')]
class BazingaController
{
    #[Route('/api/bazinga', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Success',
        properties: ['value' => new Model(type: BazingaUser::class)],
    )]
    public function userAction()
    {
    }

    #[Route('/api/bazinga_foo', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Success',
        properties: ['value' => new Model(type: BazingaUser::class, groups: ['foo'])],
    )]
    public function userGroupAction()
    {
    }
}
