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
use Symfony\Component\Routing\Annotation\Route;

class MapQueryParameterWithInvalidPCREController
{
    #[Route('/article_map_query_parameter_invalid_regexp', methods: ['GET'])]
    #[OA\Response(response: '200', description: '')]
    public function fetchArticleWithInvalidRegexp(
        #[MapQueryParameter(filter: FILTER_VALIDATE_REGEXP, options: ['regexp' => 'This is not a valid regexp'])]
        string $regexp,
    ) {
    }
}
