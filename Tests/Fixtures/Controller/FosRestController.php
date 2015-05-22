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

use Symfony\Component\Validator\Constraints\Email;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class FosRestController
{

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
     * @ApiDoc(
     *  description="Testing ApiDoc.output.groups not being set when View.serializerGroups is not defined",
     *  output="Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsTest"
     * )
     *
     * @View()
     */
    public function zActionWithViewAndNoSerializerGroups()
    {
    }

    /**
     * @ApiDoc(
     *  description="Testing View.serializerGroups setting ApiDoc.output.groups",
     *  output="Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsTest"
     * )
     *
     * @View(serializerGroups={"some-group"})
     */
    public function zActionWithViewAndSerializerGroups()
    {
    }

    /**
     * @ApiDoc(
     *  description="Testing View.serializerGroup when ApiDoc.output.class is missing"
     * )
     *
     * @View(serializerGroups={"some-group"})
     */
    public function zActionWithViewButNoOutputClass()
    {
    }

    /**
     * @ApiDoc(
     *  description="Testing groups not being overwritten with View.serializerGroups when already set in ApiDoc.output.groups",
     *  output={
     *      "class"  = "Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsTest",
     *      "groups" = {"some-other-group"}
     *  }
     * )
     *
     * @View(serializerGroups={"some-group"})
     */
    public function zActionWithViewAndGroupsInOutput()
    {
    }
}
