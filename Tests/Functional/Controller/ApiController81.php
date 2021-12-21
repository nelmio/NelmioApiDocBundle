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

use Nelmio\ApiDocBundle\Annotation\Areas;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Article;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

class ApiController81 extends ApiController80
{
    #[OA\Get([
        'value' => new OA\Response(
            response: '200',
            description: 'Success',
            properties: [
                'value' => new Model(type: Article::class, groups: ['light']),
            ],
        ),
    ])]
    #[OA\Parameter(ref: '#/components/parameters/test')]
    #[Route('/article_attributes/{id}', methods: ['GET'])]
    #[OA\Parameter(name: 'Accept-Version', in: 'header', properties: ['value' => new OA\Schema(type: 'string')])]
    public function fetchArticleActionWithAttributes()
    {
    }

    #[Areas(['area', 'area2'])]
    #[Route('/areas_attributes/new', methods: ['GET', 'POST'])]
    public function newAreaActionAttributes()
    {
    }

    #[Route('/security_attributes')]
    #[OA\Response(response: '201', description: '')]
    #[Security(name: 'api_key')]
    #[Security(name: 'basic')]
    #[Security(name: 'oauth2', scopes: ['scope_1'])]
    public function securityActionAttributes()
    {
    }
}
