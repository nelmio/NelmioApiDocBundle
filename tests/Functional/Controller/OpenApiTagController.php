<?php

declare(strict_types=1);

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
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag(name: 'My tag name', description: 'My description of the tag', externalDocs: new OA\ExternalDocumentation(url: 'https://example.com'))]
class OpenApiTagController
{
    #[Route('/some_post', methods: ['POST'])]
    #[OA\Response(response: '200', description: '')]
    public function somePost()
    {
    }

    #[Route('/some_get', methods: ['GET'])]
    #[OA\Response(response: '200', description: '')]
    public function someGet()
    {
    }
}
