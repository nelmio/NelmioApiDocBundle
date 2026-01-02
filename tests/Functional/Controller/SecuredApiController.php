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

use Nelmio\ApiDocBundle\Attribute\Security;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Article;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/secured')]
#[IsGranted('ROLE_USER')]
class SecuredApiController
{
    #[Route('/article/{id}', methods: 'GET')]
    public function fetchArticleAction()
    {
    }

    #[Route('/article', methods: 'POST')]
    #[IsGranted('ROLE_ADMIN')]
    public function newArticleAction(Article $newArticle)
    {
    }

    #[Route('/article/{id}', methods: 'PATCH')]
    #[IsGranted('ROLE_ADMIN')]
    #[IsGranted('ROLE_UPDATE_ARTICLE', subject: 'newArticle')]
    public function updateArticleAction(Article $newArticle)
    {
    }

    #[Route('/user/documentation', methods: 'GET')]
    #[OA\Get(
        security: [] // Explicitly set to empty array
    )]
    #[IsGranted('ROLE_USER')]
    public function userDocumentation(Article $newArticle)
    {
    }

    #[Route('/user/documentation/attribute', methods: 'GET')]
    #[IsGranted('ROLE_USER')]
    #[Security(
        name: 'BearerAuthCustom',
        scopes: ['read:user'],
    )]
    public function userDocumentationSecurityAttribute(Article $newArticle)
    {
    }
}
