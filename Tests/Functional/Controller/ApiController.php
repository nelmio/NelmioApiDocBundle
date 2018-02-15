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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swagger\Annotations as SWG;

/**
 * @Route("/api")
 */
class ApiController
{
    /**
     * @SWG\Response(
     *     response="200",
     *     description="Success",
     *     @Model(type=Article::class, groups={"light"})
     * )
     * @Route("/article/{id}", methods={"GET"})
     */
    public function fetchArticleAction()
    {
    }

    /**
     * @Route("/swagger", methods={"GET"})
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
     *         @Model(type=User::class)
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
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=UserType::class)
     *     )
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
     * @Route("/fosrest.{_format}", methods={"POST"})
     * @QueryParam(name="foo")
     * @RequestParam(name="bar")
     */
    public function fosrestAction()
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
     *     @Model(type=DummyType::class)
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
     *     @Model(type=SymfonyConstraints::class)
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
     * @Route("/configReference", methods={"GET"})
     */
    public function configReferenceAction()
    {
    }
}
