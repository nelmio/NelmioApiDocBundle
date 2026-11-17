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

use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Spec as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * {@see \Nelmio\ApiDocBundle\Tests\Functional\Controller\OperationIdController} with spec
 * attributes. Route names are the ones Symfony generates for the original, so both produce the
 * same fixture.
 */
class OperationIdController
{
    #[Route(path: '/generate/operation_id_route_unnamed', name: 'nelmio_apidoc_tests_functional_operationid_getmustgenerateoperationidbyunnamedrouteannotation', methods: 'GET')]
    public function getMustGenerateOperationIdByUnnamedRouteAnnotation(): JsonResponse
    {
        return new JsonResponse();
    }

    #[Route(path: '/generate/operation_id_route', name: 'named_route', methods: 'GET')]
    public function getMustGenerateOperationIdByRouteAnnotation(): JsonResponse
    {
        return new JsonResponse();
    }

    #[Route(path: '/generate/operation_id_with_security/', name: 'with_security', methods: 'GET')]
    #[OA\Operation\Get('OperationId must be generated automatically when additional OA/nelmio root annotations are present')]
    #[Security(name: 'bearerAuth')]
    public function getWithAdditionalAnnotationsGeneratesOperationId(): JsonResponse
    {
        return new JsonResponse();
    }

    #[Route(path: '/generate/operation_id_get', name: 'get_annotation', methods: 'GET')]
    #[OA\Operation\Get(summary: 'OperationId must be generated automatically if not provided')]
    public function getMustGenerateOperationIByGetAnnotation(): JsonResponse
    {
        return new JsonResponse();
    }

    #[Route(path: '/has/explicit/operationid', name: 'customOperationId', methods: 'GET')]
    #[OA\Operation\Get(summary: 'Custom operation id must be used if provided', operationId: 'customOperationId')]
    public function getWithCustomOperationId(): JsonResponse
    {
        return new JsonResponse();
    }

    #[Route(path: '/generate/operation_id_route', name: 'postOperation', methods: 'POST')]
    public function postOperation(): JsonResponse
    {
        return new JsonResponse();
    }

    #[Route(path: '/generate/operation_id_route', name: 'updateOperation', methods: 'PUT')]
    public function updateOperation(): JsonResponse
    {
        return new JsonResponse();
    }
}
