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
use Symfony\Component\Routing\Attribute\Route;

/**
 * {@see \Nelmio\ApiDocBundle\Tests\Functional\Controller\OpenApiTagController} with spec
 * attributes. Route names are the ones Symfony generates for the original, so both produce the
 * same fixture.
 */
#[OA\Tag(name: 'My tag name', description: 'My description of the tag', externalDocs: new OA\ExternalDocumentation(url: 'https://example.com'))]
class OpenApiTagController
{
    #[Route('/some_post', name: 'nelmio_apidoc_tests_functional_openapitag_somepost', methods: ['POST'])]
    #[OA\Response(response: '200', description: '')]
    public function somePost(): void
    {
    }

    #[Route('/some_get', name: 'nelmio_apidoc_tests_functional_openapitag_someget', methods: ['GET'])]
    #[OA\Response(response: '200', description: '')]
    public function someGet(): void
    {
    }
}
