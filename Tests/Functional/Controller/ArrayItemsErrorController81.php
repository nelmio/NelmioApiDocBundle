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
use OpenApi\Attributes as OAT;
use Symfony\Component\Routing\Annotation\Route;

class ArrayItemsErrorController81
{
    #[Route('/api/error', methods: ['GET'])]
    #[OAT\Response(response: 200, description: 'Success', content: new Model(type: Foo::class))]
    public function errorAction()
    {
    }
}
