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

use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/test", host="api-test.example.com")
 */
class TestController
{
    /**
     * @SWG\Response(
     *     response="200",
     *     description="Test"
     * )
     * @Route("/test/", methods={"GET"})
     */
    public function testAction()
    {
    }

    /**
     * @SWG\Parameter(ref="#/parameters/test"),
     * @SWG\Response(
     *     response="200",
     *     description="Test Ref"
     * )
     * @Route("/test/{id}", methods={"GET"})
     */
    public function testRefAction()
    {
    }
}
