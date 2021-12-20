<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\ControllerPhp81;

use Nelmio\ApiDocBundle\Annotation\Areas;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\Article;
use Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\CompoundEntity;
use Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\SymfonyConstraints;
use Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\SymfonyDiscriminator;
use Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\User;
use Nelmio\ApiDocBundle\Tests\Functional\Form\DummyType;
use Nelmio\ApiDocBundle\Tests\Functional\Form\UserType;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_', host: 'api.example.com')]
#[OA\Tag(name: 'implicit')]
class ApiController
{
    #[OA\Get([
        'value' => new OA\Response(
            response: '200',
            description: 'Success',
            properties: [
                'value' => new Model(type: Article::class, groups: ['light']),
            ],
        ),
    ])]
    #[OA\Parameter(ref: '#/components/parameters/test')]
    #[Route('/article/{id}', methods: ['GET'])]
    #[OA\Parameter(name: 'Accept-Version', in: 'header', properties: ['value' => new OA\Schema(type: 'string')])]
    public function fetchArticleAction()
    {
    }

    /**
     * The method LINK is not supported by OpenAPI so the method will be ignored.
     */
    #[Route('/swagger', methods: ['GET', 'LINK'])]
    #[Route('/swagger2', methods: ['GET'])]
    #[Operation([
        'value' => new OA\Response(response: '201', description: 'An example resource'),
    ])]
    #[OA\Get(
        path: '/api/swagger2',
        properties: ['value' => new OA\Parameter(name: 'Accept-Version', in: 'header', properties: ['value' => new OA\Schema(type: 'string')])],
    )]
    #[OA\Post(
        path: '/api/swagger2',
        properties: ['value' => new OA\Response(response: '203', description: 'but 203 is not actually allowed (wrong method)')],
    )]
    public function swaggerAction()
    {
    }

    #[Route('/swagger/implicit', methods: ['GET', 'POST'])]
    #[OA\Response(
        response: '201',
        description: 'Operation automatically detected',
        properties: ['value' => new Model(type: User::class)],
    )]
    #[OA\RequestBody(
        description: 'This is a request body',
        properties: ['value' => new OA\JsonContent(
            type: 'array',
            properties: ['value' => new OA\Items(['ref' => new Model(type: User::class)])],
        )],
    )]
    public function implicitSwaggerAction()
    {
    }

    #[Route('/test/users/{user}', methods: ['POST'], schemes: ['https'], requirements: ['user' => '/foo/'])]
    #[OA\Response(
        response: '201',
        description: 'Operation automatically detected',
        properties: ['value' => new Model(type: User::class)],
    )]
    #[OA\RequestBody(
        description: 'This is a request body',
        properties: ['value' => new Model(
            type: UserType::class,
            options: ['bar' => 'baz'],
        )],
    )]
    public function submitUserTypeAction()
    {
    }

    #[Route('/test/{user}', methods: ['GET'], schemes: ['https'], requirements: ['user' => '/foo/'])]
    #[OA\Response(response: 200, description: 'sucessful')]
    public function userAction()
    {
    }

    /**
     * This action is deprecated.
     *
     * Please do not use this action.
     *
     * @deprecated
     */
    #[Route('/deprecated', methods: ['GET'])]
    public function deprecatedAction()
    {
    }

    /**
     * This action is not documented. It is excluded by the config.
     */
    #[Route('/admin', methods: ['GET'])]
    public function adminAction()
    {
    }

    #[OA\Get(
        path: '/filtered',
        properties: ['value' => new OA\Response(response: '201', description: '')],
    )]
    public function filteredAction()
    {
    }

    #[Route('/form', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'Request content',
        properties: ['value' => new Model(type: DummyType::class)],
    )]
    #[OA\Response(response: '201', description: '')]
    public function formAction()
    {
    }

    #[Route('/security')]
    #[OA\Response(response: '201', description: '')]
    #[Security(name: 'api_key')]
    #[Security(name: 'basic')]
    #[Security(name: 'oauth2', scopes: ['scope_1'])]
    public function securityAction()
    {
    }

    #[Route('/swagger/symfonyConstraints', methods: ['GET'])]
    #[OA\Response(
        response: '201',
        description: 'Used for symfony constraints test',
        properties: ['value' => new Model(type: SymfonyConstraints::class)],
    )]
    public function symfonyConstraintsAction()
    {
    }

    #[OA\Response(
        response: '200',
        description: 'Success',
        properties: ['ref' => '#/components/schemas/Test'],
    )]
    #[OA\Response(
        response: '201',
        properties: ['ref' => '#/components/responses/201'],
    )]
    #[Route('/configReference', methods: ['GET'])]
    public function configReferenceAction()
    {
    }

    #[Route('/multi-annotations', methods: ['GET', 'POST'])]
    #[OA\Get(description: 'This is the get operation')]
    #[OA\Post(description: 'This is post')]
    #[OA\Response(response: 200, description: 'Worked well!', properties: ['value' => new Model(type: DummyType::class)])]
    public function operationsWithOtherAnnotations()
    {
    }

    #[Route('/areas/new', methods: ['GET', 'POST'])]
    #[Areas(['area', 'area2'])]
    public function newAreaAction()
    {
    }

    #[Route('/compound', methods: ['GET', 'POST'])]
    #[OA\Response(response: 200, description: 'Worked well!', properties: ['value' => new Model(type: CompoundEntity::class)])]
    public function compoundEntityAction()
    {
    }

    #[Route('/discriminator-mapping', methods: ['GET', 'POST'])]
    #[OA\Response(response: 200, description: 'Worked well!', properties: ['value' => new Model(type: SymfonyDiscriminator::class)])]
    public function discriminatorMappingAction()
    {
    }

    #[Route('/named_route-operation-id', name: 'named_route_operation_id', methods: ['GET', 'POST'])]
    #[OA\Response(response: 200, description: 'success')]
    public function namedRouteOperationIdAction()
    {
    }

    #[Route('/custom-operation-id', methods: ['GET', 'POST'])]
    #[Operation(operationId: 'custom-operation-id')]
    #[OA\Response(response: 200, description: 'success')]
    public function customOperationIdAction()
    {
    }
}
