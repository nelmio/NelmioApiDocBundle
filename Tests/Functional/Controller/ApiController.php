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

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Article;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyConstraints;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\User;
use Nelmio\ApiDocBundle\Tests\Functional\Form\DummyType;
use Nelmio\ApiDocBundle\Tests\Functional\Form\UserType;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * @Route("/api", host="api.example.com")
 */
class ApiController
{
    /**
     * @OA\Response(
     *     response="200",
     *     description="Success",
     *     @OA\JsonContent(ref=@Model(type=Article::class, groups={"light"}))
     * )
     * @OA\Parameter(ref="#/components/parameters/test")
     * @Route("/article/{id}", methods={"GET"})
     */
    public function fetchArticleAction()
    {
    }

    /**
     * The method LINK is not supported by OpenAPI so the method will be ignored.
     *
     * @Route("/swagger", methods={"GET", "LINK"})
     * @Route("/swagger2", methods={"GET"})
     * @Operation(
     *     @OA\Response(response="201", description="An example resource")
     * )
     */
    public function swaggerAction()
    {
    }

    /**
     * @Route("/swagger/implicit", methods={"GET", "POST"})
     * @OA\Response(
     *     response="201",
     *     description="Operation automatically detected",
     *     @OA\JsonContent(@Model(type=User::class))
     * )
     * @OA\Post(
     *     @OA\RequestBody(
     *         request="foo",
     *         description="This is a parameter",
     *         @OA\JsonContent(@OA\Schema(type="array", @OA\Items(ref=@Model(type=User::class))))
     *     )
     * )
     * @OA\Tag(name="implicit")
     */
    public function implicitSwaggerAction()
    {
    }

//    /**
//     * @Route("/test/users/{user}", methods={"POST"}, schemes={"https"}, requirements={"user"="/foo/"})
//     * @OA\Response(
//     *     response="201",
//     *     description="Operation automatically detected",
//     *     @OA\JsonContent(@Model(type=User::class))
//     * )
//     * @OA\Post(
//     *     @OA\RequestBody(
//     *         request="foo",
//     *         description="This is a parameter",
//     *         @OA\MediaType(
//     *             mediaType="application/json",
//     *             @OA\Schema(ref=@Model(type=UserType::class, options={"bar": "baz"}))
//     *         )
//     *     )
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
//     * @Route("/fosrest.{_format}", methods={"POST"})
//     * @QueryParam(name="foo", requirements=@Regex("/^\d+$/"))
//     * @RequestParam(name="bar", requirements="\d+")
//     * @RequestParam(name="baz", requirements=@IsTrue)
//     */
//    public function fosrestAction()
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
//     * @OA\Parameter(
//     *     name="form",
//     *     in="body",
//     *     description="Request content",
//     *     @OA\Schema(ref=@Model(type=DummyType::class))
//     * )
//     * @OA\Response(response="201", description="")
//     */
//    public function formAction()
//    {
//    }
//
//    /**
//     * @Route("/security")
//     * @OA\Response(response="201", description="")
//     * @Security(name="api_key")
//     * @Security(name="basic")
//     */
//    public function securityAction()
//    {
//    }
//
//    /**
//     * @Route("/swagger/symfonyConstraints", methods={"GET"})
//     * @OA\Response(
//     *     response="201",
//     *     description="Used for symfony constraints test",
//     *     @OA\JsonContent(ref=@Model(type=SymfonyConstraints::class))
//     * )
//     */
//    public function symfonyConstraintsAction()
//    {
//    }
//
//    /**
//     * @OA\Response(
//     *     response="200",
//     *     description="Success",
//     *     @OA\JsonContent(@OA\Schema(ref="#/definitions/Test"))
//     * )
//     * @OA\Response(
//     *     response="201",
//     *     ref="#/responses/201"
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
//     * @OA\Response(
//     *     response="200",
//     *     description="Worked well!",
//     *     @OA\JsonContent(@Model(type=DummyType::class))
//     * )
//     */
//    public function operationsWithOtherAnnotations()
//    {
//    }
}
