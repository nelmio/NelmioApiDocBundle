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
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Article;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\CompoundEntity;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyConstraints;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyDiscriminator;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\User;
use Nelmio\ApiDocBundle\Tests\Functional\Form\DummyType;
use Nelmio\ApiDocBundle\Tests\Functional\Form\UserType;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_', host: 'api.example.com')]
class ApiController
{
    /**
     * @OA\Get(
     *  @OA\Response(
     *   response="200",
     *   description="Success",
     *   @Model(type=Article::class, groups={"light"}))
     *  )
     * )
     * @OA\Parameter(ref="#/components/parameters/test")
     * @Route("/article/{id}", methods={"GET"})
     * @OA\Parameter(name="Accept-Version", in="header", @OA\Schema(type="string"))
     * @OA\Parameter(name="Application-Name", in="header", @OA\Schema(type="string"))
     */
    public function fetchArticleAction()
    {
    }

    /**
     * The method LINK is not supported by OpenAPI so the method will be ignored.
     *
     * @Operation(
     *     @OA\Response(response="201", description="An example resource")
     * )
     * @OA\Get(
     *     path="/api/swagger2",
     *     @OA\Parameter(name="Accept-Version", in="header", @OA\Schema(type="string"))
     * )
     * @OA\Post(
     *     path="/api/swagger2",
     *     @OA\Response(response="203", description="but 203 is not actually allowed (wrong method)")
     * )
     */
    #[Route(path: '/swagger', methods: ['GET', 'LINK'])]
    #[Route(path: '/swagger2', methods: ['GET'])]
    public function swaggerAction()
    {
    }

    /**
     * @OA\Response(
     *    response="201",
     *    description="Operation automatically detected",
     *    @Model(type=User::class)
     * ),
     * @OA\RequestBody(
     *    description="This is a request body",
     *    @OA\JsonContent(
     *      type="array",
     *      @OA\Items(ref=@Model(type=User::class))
     *    )
     * )
     * @OA\Tag(name="implicit")
     */
    #[Route(path: '/swagger/implicit', methods: ['GET', 'POST'])]
    public function implicitSwaggerAction()
    {
    }

    /**
     * @OA\Response(
     *    response="201",
     *    description="Operation automatically detected",
     *    @Model(type=User::class)
     * ),
     * @OA\RequestBody(
     *    description="This is a request body",
     *    @Model(type=UserType::class, options={"bar": "baz"}))
     * )
     */
    #[Route(path: '/test/users/{user}', methods: ['POST'], schemes: ['https'], requirements: ['user' => '/foo/'])]
    public function submitUserTypeAction()
    {
    }

    /**
     * @OA\Response(response=200, description="sucessful")
     */
    #[Route(path: '/test/{user}', methods: ['GET'], schemes: ['https'], requirements: ['user' => '/foo/'])]
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
    #[Route(path: '/deprecated', methods: ['GET'])]
    public function deprecatedAction()
    {
    }

    /**
     * This action is not documented. It is excluded by the config.
     */
    #[Route(path: '/admin', methods: ['GET'])]
    public function adminAction()
    {
    }

    /**
     * @OA\Get(
     *     path="/filtered",
     *     @OA\Response(response="201", description="")
     * )
     */
    public function filteredAction()
    {
    }

    /**
     * @OA\RequestBody(
     *    description="Request content",
     *    @Model(type=DummyType::class))
     * )
     * @OA\Response(response="201", description="")
     */
    #[Route(path: '/form', methods: ['POST'])]
    public function formAction()
    {
    }

    /**
     * @OA\Response(response="201", description="")
     * @Security(name="api_key")
     * @Security(name="basic")
     * @Security(name="oauth2", scopes={"scope_1"})
     */
    #[Route(path: '/security')]
    public function securityAction()
    {
    }

    /**
     * @OA\Response(
     *    response="201",
     *    description="Used for symfony constraints test",
     *    @Model(type=SymfonyConstraints::class)
     * )
     */
    #[Route(path: '/swagger/symfonyConstraints', methods: ['GET'])]
    public function symfonyConstraintsAction()
    {
    }

    /**
     *  @OA\Response(
     *     response="200",
     *     description="Success",
     *     ref="#/components/schemas/Test"
     *  ),
     *  @OA\Response(
     *     response="201",
     *     ref="#/components/responses/201"
     *  )
     */
    #[Route(path: '/configReference', methods: ['GET'])]
    public function configReferenceAction()
    {
    }

    /**
     * @OA\Get(description="This is the get operation")
     * @OA\Post(description="This is post")
     * @OA\Response(response=200, description="Worked well!", @Model(type=DummyType::class))
     */
    #[Route(path: '/multi-annotations', methods: ['GET', 'POST'])]
    public function operationsWithOtherAnnotations()
    {
    }

    /**
     * @Areas({"area", "area2"})
     */
    #[Route(path: '/areas/new', methods: ['GET', 'POST'])]
    public function newAreaAction()
    {
    }

    /**
     * @OA\Response(response=200, description="Worked well!", @Model(type=CompoundEntity::class))
     */
    #[Route(path: '/compound', methods: ['GET', 'POST'])]
    public function compoundEntityAction()
    {
    }

    /**
     * @OA\Response(response=200, description="Worked well!", @Model(type=SymfonyDiscriminator::class))
     */
    #[Route(path: '/discriminator-mapping', methods: ['GET', 'POST'])]
    public function discriminatorMappingAction()
    {
    }

    /**
     * @OA\Response(response=200, description="success")
     */
    #[Route(path: '/named_route-operation-id', name: 'named_route_operation_id', methods: ['GET', 'POST'])]
    public function namedRouteOperationIdAction()
    {
    }

    /**
     * @Operation(operationId="custom-operation-id")
     * @OA\Response(response=200, description="success")
     */
    #[Route(path: '/custom-operation-id', methods: ['GET', 'POST'])]
    public function customOperationIdAction()
    {
    }
}
