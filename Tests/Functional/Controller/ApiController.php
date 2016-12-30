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
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
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
     * @SWG\Response(response="201", description="Operation automatically detected")
     * @SWG\Parameter(
     *     name="foo",
     *     in="query",
     *     description="This is a parameter"
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
     * @Route("/nelmio/{foo}", methods={"POST"})
     * @ApiDoc(
     *   description="This action is described."
     * )
     */
    public function nelmioAction()
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
