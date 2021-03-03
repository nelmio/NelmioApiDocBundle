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
use Nelmio\ApiDocBundle\Tests\Functional\EntityExcluded\SerializedNameEnt;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This controller is only loaded when SerializedName exists (sf >= 4.2).
 *
 * @Route("/api", host="api.example.com")
 */
class SerializedNameController
{
    /**
     * @OA\Response(
     *     response="200",
     *     description="success",
     *     @Model(type=SerializedNameEnt::class)
     * )
     * @Route("/serializename", methods={"GET"})
     */
    public function serializedNameAction()
    {
    }
}
