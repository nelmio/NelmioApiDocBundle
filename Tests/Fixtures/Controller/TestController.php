<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Fixtures\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class TestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="index action",
     *  filters={
     *      {"name"="a", "dataType"="integer"},
     *      {"name"="b", "dataType"="string", "arbitrary"={"arg1", "arg2"}}
     *  }
     * )
     */
    public function indexAction()
    {
    }

    /**
     * @ApiDoc(
     *  description="create test",
     *  formType="Nelmio\ApiDocBundle\Tests\Fixtures\Form\TestType"
     * )
     */
    public function postTestAction()
    {
    }
}
