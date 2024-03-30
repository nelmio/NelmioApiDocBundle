<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Controller;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityWithPromotedPropertiesWithDefaults81;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;

class PromotedPropertiesController81
{
    #[Route('/entity-with-promoted-properties-with-defaults', methods: ['GET'])]
    #[OA\Get(
        operationId: 'getEntityWithPromotedPropertiesWithDefaults',
    )]
    #[OA\Response(
        response: 204,
        description: 'Operation automatically detected',
    )]
    #[OA\RequestBody(
        content: new Model(type: EntityWithPromotedPropertiesWithDefaults81::class),
    )]
    public function entityWithPromotedPropertiesWithDefaults()
    {
    }
}
