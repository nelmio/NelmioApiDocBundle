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
use Nelmio\ApiDocBundle\Tests\Functional\Entity\JMSUser;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Attribute\Route;

#[Route(host: 'api.example.com')]
final class JmsOptOutController
{
    #[Route('/api/jms', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new Model(type: JMSUser::class)
    )]
    public function jms(): void
    {
    }

    #[Route('/api/jms_opt_out', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new Model(type: JMSUser::class, serializationContext: ['useJms' => false])
    )]
    public function jmsOptOut(): void
    {
    }
}
