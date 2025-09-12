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
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Article;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/custom_model_names')]
final class CustomModelNameController
{
    #[Route('/unique', methods: 'POST')]
    #[OA\RequestBody(
        description: 'Request body description',
        content: new Model(type: Article::class, name: 'MyUniqueArticle'),
    )]
    public function postUniqueArticles(): void
    {
    }

    #[Route('/plain', methods: 'POST')]
    #[OA\RequestBody(
        description: 'Request body description',
        content: new Model(type: Article::class, name: 'MyPlainArticle'),
    )]
    public function postPlainArticle(): void
    {
    }

    #[Route('/plain', methods: 'GET')]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'An article',
        content: new Model(type: Article::class, name: 'MyPlainArticle'),
    )]
    public function getPlainArticle(): void
    {
    }

    #[Route('/plain/all', methods: 'GET')]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Array of articles',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Article::class, name: 'MyPlainArticle'))
        )
    )]
    public function getAllPlainArticles(): void
    {
    }

    #[Route(methods: 'GET')]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Normal article without custom model name',
        content: new Model(type: Article::class),
    )]
    public function getNormalArticle(): void
    {
    }
}
