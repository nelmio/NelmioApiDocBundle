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

use Symfony\Component\Routing\Annotation\Route;

if (\PHP_VERSION_ID >= 80100) {
    /**
     * @Route("/api", host="api.example.com")
     */
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
