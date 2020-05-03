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
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyConstraints;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\User;
use Nelmio\ApiDocBundle\Tests\Functional\Form\DummyType;
use Nelmio\ApiDocBundle\Tests\Functional\Form\UserType;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", host="api.example.com")
 */
class ApiController
{
//    /**
//     *
//     * @Operation(
//     *     @OA\Parameter(ref="#/components/parameters/test"),
//     *     @OA\Response(
//     *      response="200",
//     *      description="Success",
//     *      @OA\Schema(ref=@Model(type=Article::class, groups={"light"}))
//     *     )
//     * )
//     * @Route("/article/{id}", methods={"GET"})
//     */
//    public function fetchArticleAction()
//    {
//    }
//
//    /**
//     * The method LINK is not supported by OpenAPI so the method will be ignored.
//     *
//     * @Route("/swagger", methods={"GET", "LINK"})
//     * @Route("/swagger2", methods={"GET"})
//     * @Operation(
//     *     @OA\Response(response="201", description="An example resource")
//     * )
//     */
//    public function swaggerAction()
//    {
//    }
//
//    /**
//     * @Route("/swagger/implicit", methods={"GET", "POST"})
//     * @Operation(
//     *  @OA\Response(
//     *     response="201",
//     *     description="Operation automatically detected",
//     *     @Model(type=User::class)
//     *  ),
//     *  @OA\Parameter(
//     *     name="foo",
//     *     in="body",
//     *     description="This is a parameter",
//     *     @OA\Schema(
//     *         type="array",
//     *         @OA\Items(ref=@Model(type=User::class))
//     *     )
//     *  )
//     * )
//     * @OA\Tag(name="implicit")
//     */
//    public function implicitSwaggerAction()
//    {
//    }
//
//    /**
//     * @Route("/test/users/{user}", methods={"POST"}, schemes={"https"}, requirements={"user"="/foo/"})
//     * @Operation(
//     *  @OA\Response(
//     *     response="201",
//     *     description="Operation automatically detected",
//     *     @Model(type=User::class)
//     *  ),
//     *  @OA\Parameter(
//     *     name="foo",
//     *     in="body",
//     *     description="This is a parameter",
//     *     @OA\Schema(ref=@Model(type=UserType::class, options={"bar": "baz"}))
//     *  )
//     * )
//     */
//    public function submitUserTypeAction()
//    {
//    }
//
//    /**
//     * @Route("/test/{user}", methods={"GET"}, schemes={"https"}, requirements={"user"="/foo/"})
//     * @Operation(
//     *     @OA\Response(response=200, description="sucessful")
//     * )
//     */
//    public function userAction()
//    {
//    }
//
//    /**
//     * This action is deprecated.
//     *
//     * Please do not use this action.
//     *
//     * @Route("/deprecated", methods={"GET"})
//     *
//     * @deprecated
//     */
//    public function deprecatedAction()
//    {
//    }
//
//    /**
//     * This action is not documented. It is excluded by the config.
//     *
//     * @Route("/admin", methods={"GET"})
//     */
//    public function adminAction()
//    {
//    }
//
//    /**
//     * @OA\Get(
//     *     path="/filtered",
//     *     @OA\Response(response="201", description="")
//     * )
//     */
//    public function filteredAction()
//    {
//    }
//
//    /**
//     * @Route("/form", methods={"POST"})
//     * @Operation(
//     *  @OA\Parameter(
//     *     name="form",
//     *     in="body",
//     *     description="Request content",
//     *     @OA\Schema(ref=@Model(type=DummyType::class))
//     *  ),
//     *  @OA\Response(response="201", description="")
//     * )
//     */
//    public function formAction()
//    {
//    }
//
//    /**
//     * @Route("/security")
//     * @Operation(
//     *     @OA\Response(response="201", description="")
//     * )
//     * @Security(name="api_key")
//     * @Security(name="basic")
//     */
//    public function securityAction()
//    {
//    }
//
//    /**
//     * @Route("/swagger/symfonyConstraints", methods={"GET"})
//     * @Operation(
//     *  @OA\Response(
//     *     response="201",
//     *     description="Used for symfony constraints test",
//     *     @OA\Schema(ref=@Model(type=SymfonyConstraints::class))
//     *  )
//     * )
//     */
//    public function symfonyConstraintsAction()
//    {
//    }
//
//    /**
//     * @Operation(
//     *  @OA\Response(
//     *     response="200",
//     *     description="Success",
//     *     @OA\Schema(ref="#/components/schemas/Test")
//     *  ),
//     *  @OA\Response(
//     *     response="201",
//     *     ref="#/responses/201"
//     *  )
//     * )
//     * @Route("/configReference", methods={"GET"})
//     */
//    public function configReferenceAction()
//    {
//    }
//
//    /**
//     * @Route("/multi-annotations", methods={"GET", "POST"})
//     * @OA\Get(description="This is the get operation")
//     * @OA\Post(description="This is post")
//     *
//     * @Operation(
//     *  @OA\Response(response=200, description="Worked well!", @Model(type=DummyType::class))
//     * )
//     */
//    public function operationsWithOtherAnnotations()
//    {
//    }
//
//    /**
//     * @Route("/areas/new", methods={"GET", "POST"})
//     *
//     * @Areas({"area", "area2"})
//     */
//    public function newAreaAction()
//    {
//    }
}
