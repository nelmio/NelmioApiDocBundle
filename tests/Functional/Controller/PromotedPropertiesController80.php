<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Controller;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityWithPromotedPropertiesWithDefaults80;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

class PromotedPropertiesController80
{
    /**
     * @Route("/entity-with-promoted-properties-with-defaults", methods={"GET"})
     *
     * @OA\Get(
     *       operationId="getEntityWithPromotedPropertiesWithDefaults",
     *  )
     *
     * @OA\Response(
     *     response="204",
     *     description="Operation automatically detected",
     *  ),
     *
     * @OA\RequestBody(
     *
     *     @Model(type=EntityWithPromotedPropertiesWithDefaults80::class))
     *  )*/
    public function entityWithPromotedPropertiesWithDefaults()
    {
    }
}
