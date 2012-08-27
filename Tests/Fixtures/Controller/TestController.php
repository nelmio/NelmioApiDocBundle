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

use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

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
        return new Response('tests');
    }

    /**
     * @ApiDoc(
     *  description="create test",
     *  input="Nelmio\ApiDocBundle\Tests\Fixtures\Form\TestType"
     * )
     */
    public function postTestAction()
    {
    }

    public function anotherAction()
    {
    }

    /**
     * @ApiDoc(description="Action without HTTP verb")
     */
    public function anyAction()
    {
    }

    /**
     * This method is useful to test if the getDocComment works.
     * And, it supports multilines until the first '@' char.
     *
     * @ApiDoc()
     *
     * @param int $id   A nice comment
     * @param int $page
     */
    public function myCommentedAction()
    {
    }

    /**
     * @ApiDoc()
     */
    public function yetAnotherAction()
    {
    }

    /**
     * @ApiDoc(
     *  description="create another test",
     *  input="dependency_type"
     * )
     */
    public function anotherPostAction()
    {
    }

    /**
     * @ApiDoc()
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page of the overview.")
     */
    public function zActionWithQueryParamAction()
    {
    }

    /**
     * @ApiDoc(
     *  description="Testing JMS",
     *  input="Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsTest"
     * )
     */
    public function jmsInputTestAction()
    {
    }

    /**
     * @ApiDoc(
     *  description="Testing return",
     *  return="dependency_type"
     * )
     */
    public function jmsReturnTestAction()
    {
    }

}
