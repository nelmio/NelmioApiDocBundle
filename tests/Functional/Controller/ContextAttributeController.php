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

use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityWithClassLevelContext;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityWithContext;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityWithGroupScopedContext;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Attribute\Route;

class ContextAttributeController
{
    #[OA\Response(
        response: '200',
        description: 'Success',
        content: new Model(type: EntityWithContext::class),
    )]
    #[Route('/context-attribute', methods: ['GET'])]
    public function contextAction(): void
    {
    }

    #[OA\Response(
        response: '200',
        description: 'Success',
        content: new Model(type: EntityWithClassLevelContext::class),
    )]
    #[Route('/context-attribute-class-level', methods: ['GET'])]
    public function classLevelContextAction(): void
    {
    }

    #[OA\Response(
        response: '200',
        description: 'Success',
        content: new Model(type: EntityWithGroupScopedContext::class, groups: ['plain']),
    )]
    #[Route('/context-attribute-group-plain', methods: ['GET'])]
    public function groupScopedContextInactiveAction(): void
    {
    }

    #[OA\Response(
        response: '200',
        description: 'Success',
        content: new Model(type: EntityWithGroupScopedContext::class, groups: ['forced']),
    )]
    #[Route('/context-attribute-group-forced', methods: ['GET'])]
    public function groupScopedContextActiveAction(): void
    {
    }

    #[OA\Response(
        response: '200',
        description: 'Success',
        content: new Model(type: EntityWithGroupScopedContext::class, groups: ['plain', 'type' => ['forced']]),
    )]
    #[Route('/context-attribute-group-nested', methods: ['GET'])]
    public function groupScopedContextNestedAction(): void
    {
    }
}
