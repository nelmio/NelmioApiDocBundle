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
use Nelmio\ApiDocBundle\Tests\Fixtures\DependencyTypePath;
use Nelmio\ApiDocBundle\Tests\Fixtures\RequestParamHelper;
use Nelmio\ApiDocBundle\Util\LegacyFormHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class TestController
{
    /**
     * @ApiDoc(
     *     resource="TestResource",
     *     views="default"
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
     *  views={ "default", "premium" },
     *  input="Nelmio\ApiDocBundle\Tests\Fixtures\Form\TestType"
     * )
     */
    public function postTestAction()
    {
    }

    /**
     * @ApiDoc(
     *     description="post test 2",
     *     views={ "default", "premium" },
     *     resource=true
     * )
     */
    public function postTest2Action()
    {
    }

    /**
     * @ApiDoc(
     *  input="Nelmio\ApiDocBundle\Tests\Fixtures\Form\RequiredType"
     * )
     */
    public function requiredParametersAction()
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
     *  views= { "default", "test" },
     *  description="create another test",
     *  input=DependencyTypePath::TYPE
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
     * @ApiDoc()
     * @QueryParam(name="mail", requirements=@Email, description="Email of someone.")
     */
    public function zActionWithConstraintAsRequirements()
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
     *  output=DependencyTypePath::TYPE
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
     * @RequestParam(name="param1", requirements="string", description="Param1 description.", nullable=true)
     */
    public function zActionWithNullableRequestParamAction()
    {
    }

    /**
     * @ApiDoc()
     * @RequestParamHelper(name="param1", requirements="string", array=true)
     */
    public function zActionWithArrayRequestParamAction()
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
    public function zCachedAction()
    {
    }

    /**
     * @ApiDoc()
     * @Security("has_role('ROLE_USER')")
     */
    public function zSecuredAction()
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

    /**
     * @ApiDoc()
     * @link http://symfony.com
     */
    public function withLinkAction()
    {
    }

    /**
     * @ApiDoc(
     *     output="Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsTest",
     *     input={
     *         "class" = "Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsTest"
     *     },
     *     parameters={
     *          {
     *              "name"="number",
     *              "dataType"="integer",
     *              "actualType"="string",
     *              "subType"=null,
     *              "required"=true,
     *              "description"="This is the new description",
     *              "readonly"=false,
     *              "sinceVersion"="v3.0",
     *              "untilVersion"="v4.0"
     *          },
     *          {
     *              "name"="arr",
     *              "dataType"="object (ArrayCollection)"
     *          },
     *          {
     *              "name"="nested",
     *              "dataType"="object (JmsNested)",
     *              "children": {
     *                  "bar": {
     *                      "dataType"="integer",
     *                      "format"="d+"
     *                  }
     *              }
     *          }
     *     }
     * )
     */
    public function overrideJmsAnnotationWithApiDocParametersAction()
    {
    }

    /**
     * @ApiDoc(
     *     output="Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsTest",
     *     input={
     *         "class" = "Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsTest"
     *     }
     * )
     */
    public function defaultJmsAnnotations()
    {
    }

    /**
     * @ApiDoc(
     *  description="Route with host placeholder",
     *  views={ "default" }
     * )
     */
    public function routeWithHostAction()
    {
    }
}
