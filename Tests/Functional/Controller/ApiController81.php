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
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Article81;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyMapQueryString;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

class ApiController81 extends ApiController80
{
    #[OA\Get(responses: [
        new OA\Response(
            response: '200',
            description: 'Success',
            attachables: [
                new Model(type: Article::class, groups: ['light']),
            ],
        ),
    ])]
    #[OA\Parameter(ref: '#/components/parameters/test')]
    #[Route('/article_attributes/{id}', methods: ['GET'])]
    #[OA\Parameter(name: 'Accept-Version', in: 'header', attachables: [new OA\Schema(type: 'string')])]
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

    #[Route('/security_override_attributes')]
    #[OA\Response(response: '201', description: '')]
    #[Security(name: 'api_key')]
    #[Security(name: null)]
    public function securityOverrideActionAttributes()
    {
    }

    #[Route('/inline_path_parameters')]
    #[OA\Response(response: '200', description: '')]
    public function inlinePathParameters(
        #[OA\PathParameter] string $product_id
    ) {
    }

    #[Route('/enum')]
    #[OA\Response(response: '201', description: '', attachables: [new Model(type: Article81::class)])]
    public function enum()
    {
    }

    #[Route('/article_map_query_string')]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryString(
        #[MapQueryString] SymfonyMapQueryString $article81Query
    ) {
    }

    #[Route('/article_map_query_string_nullable')]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryStringNullable(
        #[MapQueryString] ?SymfonyMapQueryString $article81Query
    ) {
    }

    #[Route('/article_map_query_string_overwrite_parameters')]
    #[OA\Parameter(
        name: 'id',
        in: 'query',
        description: 'Query parameter id description'
    )]
    #[OA\Parameter(
        name: 'name',
        in: 'query',
        description: 'Query parameter name description'
    )]
    #[OA\Parameter(
        name: 'nullableName',
        in: 'query',
        description: 'Query parameter nullableName description'
    )]
    #[OA\Parameter(
        name: 'articleType81',
        in: 'query',
        description: 'Query parameter articleType81 description'
    )]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryStringOverwriteParameters(
        #[MapQueryString] SymfonyMapQueryString $article81Query
    ) {
    }

    #[Route('/article_map_query_parameter')]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryParameter(
        #[MapQueryParameter] int $id,
    ) {
    }

    #[Route('/article_map_query_parameter_nullable')]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryParameterNullable(
        #[MapQueryParameter] ?int $id,
    ) {
    }

    #[Route('/article_map_query_parameter_default')]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryParameterDefault(
        #[MapQueryParameter] int $id = 123,
    ) {
    }

    #[Route('/article_map_query_parameter_overwrite_parameters')]
    #[OA\Parameter(
        name: 'id',
        in: 'query',
        description: 'Query parameter id description',
        example: 123,
    )]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryParameterOverwriteParameters(
        #[MapQueryParameter] ?int $id,
    ) {
    }
}
