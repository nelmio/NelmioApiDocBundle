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
use Nelmio\ApiDocBundle\Tests\Functional\TestKernel;
use Symfony\Component\Routing\Annotation\Route;

if (TestKernel::isAnnotationsAvailable()) {
    if (TestKernel::isAttributesAvailable()) {
        #[Route('/api', name: 'api_', host: 'api.example.com')]
        class ApiController extends ApiController80
        {
            #[\OpenApi\Attributes\Get(responses: [
                new \OpenApi\Attributes\Response(
                    response: '200',
                    description: 'Success',
                    attachables: [
                        new Model(type: Article::class, groups: ['light']),
                    ],
                ),
            ])]
            #[\OpenApi\Attributes\Parameter(ref: '#/components/parameters/test')]
            #[Route('/article_attributes/{id}', methods: ['GET'])]
            #[\OpenApi\Attributes\Parameter(name: 'Accept-Version', in: 'header', schema: new \OpenApi\Attributes\Schema(type: 'string'))]
            public function fetchArticleActionWithAttributes()
            {
            }

            #[Areas(['area', 'area2'])]
            #[Route('/areas_attributes/new', methods: ['GET', 'POST'])]
            public function newAreaActionAttributes()
            {
            }

            #[Route('/security_attributes')]
            #[\OpenApi\Attributes\Response(response: '201', description: '')]
            #[Security(name: 'api_key')]
            #[Security(name: 'basic')]
            #[Security(name: 'oauth2', scopes: ['scope_1'])]
            public function securityActionAttributes()
            {
            }

            #[Route('/security_override_attributes')]
            #[\OpenApi\Attributes\Response(response: '201', description: '')]
            #[Security(name: 'api_key')]
            #[Security(name: null)]
            public function securityOverrideActionAttributes()
            {
            }

            #[Route('/inline_path_parameters')]
            #[\OpenApi\Attributes\Response(response: '200', description: '')]
            public function inlinePathParameters(
                #[\OpenApi\Attributes\PathParameter] string $product_id
            ) {
            }

            #[Route('/enum')]
            #[\OpenApi\Attributes\Response(response: '201', description: '', attachables: [new Model(type: Article81::class)])]
            public function enum()
            {
            }
        }
    } else {
        /**
         * @Route("/api", name="api_", host="api.example.com")
         */
        class ApiController extends ApiController80
        {
        }
    }
} else {
    #[Route('/api', name: 'api_', host: 'api.example.com')]
    class ApiController extends ApiController81
    {
    }
}
