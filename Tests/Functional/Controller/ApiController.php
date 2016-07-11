<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\Tests\Functional\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ApiController
{
    /**
     * @Route("/test/{user}", methods={"GET"}, schemes={"https"}, requirements={"user"="/foo/"})
     */
    public function userAction()
    {
    }
}
