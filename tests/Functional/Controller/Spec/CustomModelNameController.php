<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Controller\Spec;

use Nelmio\ApiDocBundle\Attribute\Spec\Model;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Article;
use OpenApi\Spec as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * {@see \Nelmio\ApiDocBundle\Tests\Functional\Controller\CustomModelNameController} with spec
 * attributes. Route names are the ones Symfony generates for the original, so both produce the
 * same fixture.
 */
#[Route('/custom_model_names')]
final class CustomModelNameController
{
    #[Route('/unique', name: 'nelmio_apidoc_tests_functional_custommodelname_postuniquearticles', methods: 'POST')]
    #[OA\RequestBody(
        description: 'Request body description',
        content: new Model(type: Article::class, name: 'MyUniqueArticle'),
    )]
    public function postUniqueArticles(): void
    {
    }

    #[Route('/plain', name: 'nelmio_apidoc_tests_functional_custommodelname_postplainarticle', methods: 'POST')]
    #[OA\RequestBody(
        description: 'Request body description',
        content: new Model(type: Article::class, name: 'MyPlainArticle'),
    )]
    public function postPlainArticle(): void
    {
    }

    #[Route('/plain', name: 'nelmio_apidoc_tests_functional_custommodelname_getplainarticle', methods: 'GET')]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'An article',
        content: new Model(type: Article::class, name: 'MyPlainArticle'),
    )]
    public function getPlainArticle(): void
    {
    }

    #[Route('/plain/all', name: 'nelmio_apidoc_tests_functional_custommodelname_getallplainarticles', methods: 'GET')]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Array of articles',
        content: new OA\MediaType\Json(
            type: 'array',
            items: new Model(type: Article::class, name: 'MyPlainArticle'),
        ),
    )]
    public function getAllPlainArticles(): void
    {
    }

    #[Route(name: 'nelmio_apidoc_tests_functional_custommodelname_getnormalarticle', methods: 'GET')]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Normal article without custom model name',
        content: new Model(type: Article::class),
    )]
    public function getNormalArticle(): void
    {
    }
}
