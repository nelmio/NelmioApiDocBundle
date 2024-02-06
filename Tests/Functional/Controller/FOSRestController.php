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

use Nelmio\ApiDocBundle\Tests\Functional\TestKernel;
use Symfony\Component\Routing\Annotation\Route;

if (TestKernel::isAttributesAvailable()) {
    #[Route('/api', host: 'api.example.com')]
    class FOSRestController extends FOSRestController81
    {
    }
} else {
    /**
     * @Route("/api", host="api.example.com")
     */
    class FOSRestController extends FOSRestController80
    {
    }
}
