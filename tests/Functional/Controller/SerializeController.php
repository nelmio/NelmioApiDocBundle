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

use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SerializeArticle;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

class SerializeController
{
    #[Route('/serialize', methods: ['GET'])]
    #[Serialize]
    public function bare(): SerializeArticle
    {
        return new SerializeArticle();
    }

    #[Route('/serialize_created', methods: ['POST'])]
    #[Serialize(code: 201)]
    public function created(): SerializeArticle
    {
        return new SerializeArticle();
    }

    #[Route('/serialize_groups', methods: ['GET'])]
    #[Serialize(context: ['groups' => ['read']])]
    public function groups(): SerializeArticle
    {
        return new SerializeArticle();
    }

    #[Route('/serialize_headers', methods: ['GET'])]
    #[Serialize(headers: ['X-Custom-Header' => 'abc', 'Content-Type' => 'application/json'])]
    public function headers(): SerializeArticle
    {
        return new SerializeArticle();
    }

    /**
     * @return SerializeArticle[]
     */
    #[Route('/serialize_brackets', methods: ['GET'])]
    #[Serialize]
    public function brackets(): array
    {
        return [];
    }

    /**
     * @return list<SerializeArticle>
     */
    #[Route('/serialize_list', methods: ['GET'])]
    #[Serialize]
    public function list(): array
    {
        return [];
    }

    #[Route('/serialize_untyped_array', methods: ['GET'])]
    #[Serialize]
    public function untypedArray(): array
    {
        return [];
    }

    #[Route('/serialize_nullable', methods: ['GET'])]
    #[Serialize]
    public function nullable(): ?SerializeArticle
    {
        return null;
    }

    #[Route('/serialize_void', methods: ['DELETE'])]
    #[Serialize(code: 204)]
    public function noContent(): void
    {
    }

    #[Route('/serialize_response', methods: ['GET'])]
    #[Serialize]
    public function alreadyAResponse(): Response
    {
        return new Response();
    }

    #[Route('/serialize_scalar', methods: ['GET'])]
    #[Serialize]
    public function scalar(): string
    {
        return 'hello';
    }

    #[Route('/serialize_no_return_type', methods: ['GET'])]
    #[Serialize]
    public function noReturnType()
    {
        return null;
    }

    #[Route('/serialize_with_oa_response', methods: ['GET'])]
    #[Serialize]
    #[OA\Response(response: 200, description: 'Custom description')]
    public function withExplicitResponse(): SerializeArticle
    {
        return new SerializeArticle();
    }

    #[Route('/serialize_overwritten', methods: ['GET'])]
    #[Serialize(context: ['groups' => ['read']])]
    #[OA\Response(response: 200, description: '', content: new Model(type: SerializeArticle::class, groups: ['detail']))]
    public function overwritten(): SerializeArticle
    {
        return new SerializeArticle();
    }

    #[Route('/serialize_multiple_methods', methods: ['GET', 'POST'])]
    #[Serialize(code: 202)]
    public function multipleMethods(): SerializeArticle
    {
        return new SerializeArticle();
    }
}
