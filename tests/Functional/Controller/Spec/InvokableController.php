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

use OpenApi\Spec as OA;
use Symfony\Component\Routing\Attribute\Route;

/**
 * {@see \Nelmio\ApiDocBundle\Tests\Functional\Controller\InvokableController} with spec
 * attributes.
 */
#[OA\Response(response: 200, description: 'Invokable!')]
#[Route('/api/invoke', host: 'api.example.com', name: 'invokable', methods: ['GET'])]
class InvokableController
{
    public function __invoke(): void
    {
    }
}
