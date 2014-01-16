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
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class TestController
{
    /**
     * @ApiDoc(
     *     resource="TestResource"
     * )
     */
    public function namedResourceAction()
    {
    }

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

    /**
     * @ApiDoc(
     *     description="post test 2",
     *     resource=true
     * )
     */
    public function postTest2Action()
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
     * @param int $id        A nice comment
     * @param int $page
     * @param int $paramType The param type
     * @param int $param     The param id
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
     * @QueryParam(strict=true, name="page", requirements="\d+", description="Page of the overview.")
     */
    public function zActionWithQueryParamStrictAction()
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
     * @ApiDoc()
     * @QueryParam(name="page", requirements="\d+", description="Page of the overview.")
     */
    public function zActionWithQueryParamNoDefaultAction()
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
     *  output="dependency_type"
     * )
     */
    public function jmsReturnTestAction()
    {
    }

    /**
     * @ApiDoc()
     * @RequestParam(name="param1", requirements="string", description="Param1 description.")
     */
    public function zActionWithRequestParamAction()
    {
    }

    /**
     * @ApiDoc()
     */
    public function secureRouteAction()
    {
    }

    /**
     * @ApiDoc(
     *  authentication=true,
     *  authenticationRoles={"ROLE_USER","ROLE_FOOBAR"}
     * )
     */
    public function authenticatedAction()
    {
    }

    /**
     * @ApiDoc()
     * @Cache(maxage=60, public=1)
     */
    public function cachedAction()
    {
    }

    /**
     * @ApiDoc()
     * @deprecated
     */
    public function deprecatedAction()
    {
    }

    /**
     * @ApiDoc(
     *     output="Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsTest"
     * )
     */
    public function jmsReturnNestedOutputAction()
    {
    }

    /**
     * @ApiDoc(
     *     output="Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsChild"
     * )
     */
    public function jmsReturnNestedExtendedOutputAction()
    {
    }

    /**
     * @ApiDoc(
     *     output="Nelmio\ApiDocBundle\Tests\Fixtures\Model\MultipleTest"
     * )
     */
    public function zReturnJmsAndValidationOutputAction()
    {
    }

    /**
     * @ApiDoc(
     *  description="Returns a collection of Object",
     *  requirements={
     *      {"name"="limit", "dataType"="integer", "requirement"="\d+", "description"="how many objects to return"}
     *  },
     *  parameters={
     *      {"name"="categoryId", "dataType"="integer", "required"=true, "description"="category id"}
     *  }
     * )
     */
    public function cgetAction($id)
    {
    }

    /**
     * @ApiDoc(
     *     input={
     *          "class"="Nelmio\ApiDocBundle\Tests\Fixtures\Form\TestType",
     *          "parsers"={
     *              "Nelmio\ApiDocBundle\Parser\FormTypeParser",
     *          }
     *     }
     * )
     */
    public function zReturnSelectedParsersInputAction()
    {
    }

    /**
     * @ApiDoc(
     *     output={
     *          "class"="Nelmio\ApiDocBundle\Tests\Fixtures\Model\MultipleTest",
     *          "parsers"={
     *              "Nelmio\ApiDocBundle\Parser\JmsMetadataParser",
     *              "Nelmio\ApiDocBundle\Parser\ValidationParser"
     *          }
     *     }
     * )
     */
    public function zReturnSelectedParsersOutputAction()
    {
    }

    /**
     * @ApiDoc(
     *     section="private"
     * )
     */
    public function privateAction()
    {
    }

    /**
     * @ApiDoc(
     *     section="exclusive"
     * )
     */
    public function exclusiveAction()
    {
    }
}
