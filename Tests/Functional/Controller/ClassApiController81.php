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
use OpenApi\Attributes as OAT;
use Symfony\Component\Routing\Annotation\Route;

#[Security(name: 'basic')]
#[Route('/api', host: 'api.example.com')]
class ClassApiController81
{
    #[OAT\Response(response: 201, description: '')]
    #[Route('/security/class')]
    public function securityAction()
    {
    }
}
