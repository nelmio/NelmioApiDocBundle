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
use Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\ArrayItemsError\Foo;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

#[Route(host: 'api.example.com')]
class ArrayItemsErrorController
{
    #[OA\Response(
        response: 200,
        description: 'Success',
        properties: ['value' => new Model(type: Foo::class)],
    )]
    #[Route('/api/error', methods: ['GET'])]
    public function errorAction()
    {
    }
}
