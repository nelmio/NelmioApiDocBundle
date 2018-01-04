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

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\JMSComplex;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\JMSUser;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\VirtualProperty;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swagger\Annotations as SWG;

class JMSController
{
    /**
     * @Route("/api/jms", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=JMSUser::class)
     * )
     */
    public function userAction()
    {
    }

    /**
     * @Route("/api/yaml", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=VirtualProperty::class)
     * )
     */
    public function yamlAction()
    {
    }

    /**
     * @Route("/api/jms_complex", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=JMSComplex::class, groups={"list", "details", "user" : {"list"}})
     * )
     */
    public function complexAction()
    {
    }
}
