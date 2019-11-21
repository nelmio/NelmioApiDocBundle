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
use Nelmio\ApiDocBundle\Tests\Functional\Entity\JMSDualComplex;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\JMSNamingStrategyConstraints;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\JMSUser;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\NestedGroup\JMSChat;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\NestedGroup\JMSChatRoomUser;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\NestedGroup\JMSChatUser;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\NestedGroup\JMSPicture;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\VirtualProperty;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(host="api.example.com")
 */
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
     *     @Model(type=JMSComplex::class, groups={"list", "details", "User" : {"list"}})
     * )
     */
    public function complexAction()
    {
    }

    /**
     * @Route("/api/jms_complex_dual", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=JMSDualComplex::class, groups={"Default", "complex" : {"User" : {"details"}}})
     * )
     */
    public function complexDualAction()
    {
    }

    /**
     * @Route("/api/jms_naming_strategy", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=JMSNamingStrategyConstraints::class, groups={"Default"})
     * )
     */
    public function namingStrategyConstraintsAction()
    {
    }

    /**
     * @Route("/api/jms_chat", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=JMSChat::class, groups={"Default", "members" : {"mini"}})
     * )
     */
    public function chatAction()
    {
    }

    /**
     * @Route("/api/jms_picture", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=JMSPicture::class, groups={"mini"})
     * )
     */
    public function pictureAction()
    {
    }

    /**
     * @Route("/api/jms_mini_user", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=JMSChatUser::class, groups={"mini"})
     * )
     */
    public function minUserAction()
    {
    }

    /**
     * @Route("/api/jms_mini_user_nested", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=JMSChatRoomUser::class, groups={"mini", "friend": {"living":{"Default"}}})
     * )
     */
    public function minUserNestedAction()
    {
    }
}
