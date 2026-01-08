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

use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class MapQueryParameterController
{
    #[Route('/article_map_query_parameter', methods: ['GET'])]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryParameter(
        #[MapQueryParameter]
        int $someInt,
        #[MapQueryParameter]
        float $someFloat,
        #[MapQueryParameter]
        bool $someBool,
        #[MapQueryParameter]
        string $someString,
        #[MapQueryParameter]
        array $someArray,
    ) {
    }

    #[Route('/article_map_query_parameter_validate_filters', methods: ['GET'])]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryParameterValidateFilters(
        #[MapQueryParameter(options: ['min_range' => 2, 'max_range' => 1234])]
        int $minMaxInt,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_DOMAIN)]
        string $domain,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_EMAIL)]
        string $email,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_IP)]
        string $ip,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_IP, flags: \FILTER_FLAG_IPV4)]
        string $ipv4,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_IP, flags: \FILTER_FLAG_IPV6)]
        string $ipv6,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_MAC)]
        string $macAddress,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^test/'])]
        string $regexp,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_URL)]
        string $url,
    ): void {
    }

    #[Route('/article_map_query_parameter_nullable', methods: ['GET'])]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryParameterNullable(
        #[MapQueryParameter]
        ?int $id,
    ) {
    }

    #[Route('/article_map_query_parameter_default', methods: ['GET'])]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryParameterDefault(
        #[MapQueryParameter]
        int $id = 123,
    ) {
    }

    #[Route('/article_map_query_parameter_overwrite_parameters', methods: ['GET'])]
    #[OA\Parameter(
        name: 'id',
        in: 'query',
        description: 'Query parameter id description',
        example: 123,
    )]
    #[OA\Parameter(
        name: 'changedType',
        in: 'query',
        schema: new OA\Schema(type: 'integer', nullable: false),
        description: 'Transform the type of the query parameter',
        example: 123,
    )]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleFromMapQueryParameterOverwriteParameters(
        #[MapQueryParameter]
        ?int $id,
        #[MapQueryParameter]
        ?string $changedType,
    ): void {
    }

    #[Route('/article_map_query_parameter_invalid_regexp', methods: ['GET'])]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleWithInvalidRegexp(
        #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => 'This is not a valid regexp'])]
        string $regexp,
    ): void {
    }

    #[Route('/article_map_query_parameter_unsupported_flag', methods: ['GET'])]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleWithUnsupportedRegexpFlag(
        #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/\ZUnsupportedFlag/'])]
        string $regexp,
    ): void {
    }

    #[Route('/article_map_query_parameter_replaced_flag', methods: ['GET'])]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleWithReplacedRegexpFlag(
        #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/\ADifferentFlag/'])]
        string $regexp,
    ): void {
    }

    #[Route('/article_map_query_parameter_name_from_context', methods: ['GET'])]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleWithParameterNamesFromContext(
        #[MapQueryParameter]
        #[OA\QueryParameter(
            description: 'User ID from parameter context',
            schema: new OA\Schema(type: 'integer', minimum: 1)
        )]
        int $userId,
        #[MapQueryParameter]
        #[OA\QueryParameter(
            description: 'Search query from parameter context',
            schema: new OA\Schema(type: 'string', minLength: 3)
        )]
        string $searchQuery,
        #[MapQueryParameter]
        #[OA\QueryParameter(
            description: 'Page number from parameter context',
            schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)
        )]
        int $pageNumber,
    ): void {
    }
}
