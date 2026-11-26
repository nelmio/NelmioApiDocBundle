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

use OpenApi\Spec as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * {@see \Nelmio\ApiDocBundle\Tests\Functional\Controller\AbstractDocumentController} with spec
 * attributes.
 */
abstract class AbstractDocumentController
{
    #[Route('', name: 'nelmio_apidoc_tests_functional_invoicedocument_getdocuments', methods: ['GET'])]
    #[OA\Response(response: 200, description: 'List documents')]
    public function getDocuments(): JsonResponse
    {
    }
}
