<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Controller;

use Nelmio\ApiDocBundle\Tests\Functional\Entity\QueryModel\ArrayQueryModel;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\QueryModel\FilterQueryModel;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\QueryModel\PaginationQueryModel;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\QueryModel\SortQueryModel;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyConstraintsWithValidationGroups;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyMapQueryString;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

class MapQueryStringController
{
    #[Route('/article_map_query_string', methods: ['GET'])]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryString(
        #[MapQueryString] SymfonyMapQueryString $article81Query
    ) {
    }

    #[Route('/article_map_query_string_nullable', methods: ['GET'])]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryStringNullable(
        #[MapQueryString] ?SymfonyMapQueryString $article81Query
    ) {
    }

    #[Route('/article_map_query_string_passes_validation_groups', methods: ['GET'])]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryStringHandlesValidationGroups(
        #[MapQueryString(validationGroups: ['test'])] SymfonyConstraintsWithValidationGroups $symfonyConstraintsWithValidationGroups,
    ) {
    }

    #[Route('/article_map_query_string_overwrite_parameters', methods: ['GET'])]
    #[OA\Parameter(
        name: 'id',
        in: 'query',
        schema: new OA\Schema(type: 'string', nullable: true),
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
    #[OA\Parameter(
        name: 'nullableArticleType81',
        in: 'query',
        description: 'Query parameter nullableArticleType81 description'
    )]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryStringOverwriteParameters(
        #[MapQueryString] SymfonyMapQueryString $article81Query
    ) {
    }

    #[Route('/article_map_query_string_many_parameters', methods: ['GET'])]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleWithManyParameters(
        #[MapQueryString] FilterQueryModel $filterQuery,
        #[MapQueryString] PaginationQueryModel $paginationQuery,
        #[MapQueryString] SortQueryModel $sortQuery,
        #[MapQueryString] ArrayQueryModel $arrayQuery,
    ) {
    }

    #[Route('/article_map_query_string_many_parameters_optional', methods: ['GET'])]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleWithManyOptionalParameters(
        #[MapQueryString] ?FilterQueryModel $filterQuery,
        #[MapQueryString] ?PaginationQueryModel $paginationQuery,
        #[MapQueryString] ?SortQueryModel $sortQuery,
        #[MapQueryString] ?ArrayQueryModel $arrayQuery,
    ) {
    }
}
