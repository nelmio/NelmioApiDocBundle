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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swagger\Annotations as SWG;

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
}
