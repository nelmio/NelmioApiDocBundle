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
use Nelmio\ApiDocBundle\Tests\Functional\Entity\DiscriminatorMap\JMSAbstractUser;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\JMSComplex80;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\JMSDualComplex;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\JMSNamingStrategyConstraints;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\JMSUser;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\NestedGroup\JMSChat;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\NestedGroup\JMSChatRoomUser;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\NestedGroup\JMSChatUser;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\NestedGroup\JMSPicture;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\VirtualProperty;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

class JMSController80
{
    /**
     * @Route("/api/jms", methods={"GET"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @Model(type=JMSUser::class)
     * )
     */
    public function userAction()
    {
    }

    /**
     * @Route("/api/yaml", methods={"GET"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @Model(type=VirtualProperty::class)
     * )
     */
    public function yamlAction()
    {
    }

    /**
     * @Route("/api/jms_complex", methods={"GET"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @Model(type=JMSComplex80::class, groups={"list", "details", "User" : {"list"}})
     * )
     */
    public function complexAction()
    {
    }

    /**
     * @Route("/api/jms_complex_dual", methods={"GET"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @Model(type=JMSDualComplex::class, groups={"Default", "complex" : {"User" : {"details"}}})
     * )
     */
    public function complexDualAction()
    {
    }

    /**
     * @Route("/api/jms_naming_strategy", methods={"GET"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @Model(type=JMSNamingStrategyConstraints::class, groups={"Default"})
     * )
     */
    public function namingStrategyConstraintsAction()
    {
    }

    /**
     * @Route("/api/jms_chat", methods={"GET"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @Model(type=JMSChat::class, groups={"Default", "members" : {"mini"}})
     * )
     */
    public function chatAction()
    {
    }

    /**
     * @Route("/api/jms_picture", methods={"GET"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @Model(type=JMSPicture::class, groups={"mini"})
     * )
     */
    public function pictureAction()
    {
    }

    /**
     * @Route("/api/jms_mini_user", methods={"GET"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @Model(type=JMSChatUser::class, groups={"mini"})
     * )
     */
    public function minUserAction()
    {
    }

    /**
     * @Route("/api/jms_mini_user_nested", methods={"GET"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @Model(type=JMSChatRoomUser::class, groups={"mini", "friend": {"living":{"Default"}}})
     * )
     */
    public function minUserNestedAction()
    {
    }

    /**
     * @Route("/api/jms_discriminator_map", methods={"GET"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @Model(type=JMSAbstractUser::class)
     * )
     */
    public function discriminatorMapAction()
    {
    }
}
