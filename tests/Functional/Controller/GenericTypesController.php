<?php

declare(strict_types=1);

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
use Nelmio\ApiDocBundle\Tests\Functional\Entity\GenericTypes;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Attribute\Route;

class GenericTypesController
{
    #[OA\Response(
        response: '200',
        description: 'Success',
        content: new Model(type: GenericTypes::class),
    )]
    #[Route('/generic-types', methods: ['GET'])]
    public function genericTypesAction()
    {
    }
}
