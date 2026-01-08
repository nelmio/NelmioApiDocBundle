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

use Nelmio\ApiDocBundle\Tests\Functional\Entity\Article81;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class MapRequestPayloadArray
{
    #[Route('/article_map_request_payload_array', methods: ['POST'])]
    #[OA\Response(response: '200', description: '')]
    public function createArticleFromMapRequestPayloadArray(
        #[MapRequestPayload(type: Article81::class)]
        array $articles,
    ): void {
    }

    #[Route('/article_map_request_payload_nullable_array', methods: ['POST'])]
    #[OA\Response(response: '200', description: '')]
    public function createArticleFromMapRequestPayloadNullableArray(
        #[MapRequestPayload(type: Article81::class)]
        ?array $nullableArticles,
    ): void {
    }
}
