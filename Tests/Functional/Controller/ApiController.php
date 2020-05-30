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
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", host="api.example.com")
 */
class ApiController
{
    /**
     * @SWG\Response(
     *     response="200",
     *     description="Success",
     *     @SWG\Schema(ref=@Model(type=Article::class, groups={"light"}))
     * )
     * @SWG\Parameter(ref="#/parameters/test")
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
     *     @SWG\Response(response="201", description="An example resource")
     * )
     */
    public function swaggerAction()
    {
    }

    /**
     * @Route("/swagger/implicit", methods={"GET", "POST"})
     * @SWG\Response(
     *     response="201",
     *     description="Operation automatically detected",
     *     @Model(type=User::class)
     * )
     * @SWG\Parameter(
     *     name="foo",
     *     in="body",
     *     description="This is a parameter",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class))
     *     )
     * )
     * @SWG\Tag(name="implicit")
     */
    public function implicitSwaggerAction()
    {
    }

    /**
     * @Route("/test/users/{user}", methods={"POST"}, schemes={"https"}, requirements={"user"="/foo/"})
     * @SWG\Response(
     *     response="201",
     *     description="Operation automatically detected",
     *     @Model(type=User::class)
     * )
     * @SWG\Parameter(
     *     name="foo",
     *     in="body",
     *     description="This is a parameter",
     *     @SWG\Schema(ref=@Model(type=UserType::class, options={"bar": "baz"}))
     * )
     */
    public function submitUserTypeAction()
    {
    }

    /**
     * @Route("/test/{user}", methods={"GET"}, schemes={"https"}, requirements={"user"="/foo/"})
     * @Operation(
     *     @SWG\Response(response=200, description="sucessful")
     * )
     */
    public function userAction()
    {
    }

    /**
     * This action is deprecated.
     *
     * Please do not use this action.
     *
     * @Route("/deprecated", methods={"GET"})
     *
     * @deprecated
     */
    public function deprecatedAction()
    {
    }

    /**
     * This action is not documented. It is excluded by the config.
     *
     * @Route("/admin", methods={"GET"})
     */
    public function adminAction()
    {
    }

    /**
     * @SWG\Get(
     *     path="/filtered",
     *     @SWG\Response(response="201", description="")
     * )
     */
    public function filteredAction()
    {
    }

    /**
     * @Route("/form", methods={"POST"})
     * @SWG\Parameter(
     *     name="form",
     *     in="body",
     *     description="Request content",
     *     @SWG\Schema(ref=@Model(type=DummyType::class))
     * )
     * @SWG\Response(response="201", description="")
     */
    public function formAction()
    {
    }

    /**
     * @Route("/security")
     * @SWG\Response(response="201", description="")
     * @Security(name="api_key")
     * @Security(name="basic")
     */
    public function securityAction()
    {
    }

    /**
     * @Route("/swagger/symfonyConstraints", methods={"GET"})
     * @SWG\Response(
     *     response="201",
     *     description="Used for symfony constraints test",
     *     @SWG\Schema(ref=@Model(type=SymfonyConstraints::class))
     * )
     */
    public function symfonyConstraintsAction()
    {
    }

    /**
     * @SWG\Response(
     *     response="200",
     *     description="Success",
     *     @SWG\Schema(ref="#/definitions/Test")
     * )
     * @SWG\Response(
     *     response="201",
     *     ref="#/responses/201"
     * )
     * @Route("/configReference", methods={"GET"})
     */
    public function configReferenceAction()
    {
    }

    /**
     * @Route("/multi-annotations", methods={"GET", "POST"})
     * @SWG\Get(description="This is the get operation")
     * @SWG\Post(description="This is post")
     *
     * @SWG\Response(response=200, description="Worked well!", @Model(type=DummyType::class))
     */
    public function operationsWithOtherAnnotations()
    {
    }

    /**
     * @Route("/areas/new", methods={"GET", "POST"})
     *
     * @Areas({"area", "area2"})
     */
    public function newAreaAction()
    {
    }
}
