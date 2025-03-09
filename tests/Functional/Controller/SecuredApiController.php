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

use Nelmio\ApiDocBundle\Tests\Functional\Entity\Article;
use Symfony\Component\Routing\Annotation\Route;
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
}
