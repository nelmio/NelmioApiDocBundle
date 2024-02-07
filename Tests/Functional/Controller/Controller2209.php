<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Controller;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Article81;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class Controller2209
{
    #[Route(path: '/api/v3/users', name: 'api_v3_users_create', methods: 'POST')]
    #[OA\RequestBody(
        content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(
            ref: new Model(type: Article81::class),
        )),
    )]
    public function __invoke(#[MapRequestPayload] Article81 $requestDTO): JsonResponse
    {
    }
}
