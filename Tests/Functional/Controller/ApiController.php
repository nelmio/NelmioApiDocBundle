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
use Nelmio\ApiDocBundle\Tests\Functional\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swagger\Annotations as SWG;

/**
 * @Route("/api")
 */
class ApiController
{
    /**
     * @Route("/swagger", methods={"GET"})
     * @Route("/swagger2", methods={"GET"})
     * @SWG\Get(
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
     */
    public function implicitSwaggerAction()
    {
    }

    /**
     * @Route("/test/{user}", methods={"GET"}, schemes={"https"}, requirements={"user"="/foo/"})
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
}
