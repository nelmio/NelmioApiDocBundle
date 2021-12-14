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
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

#[Route(host: 'api.example.com')]
class JMSController
{
    /**
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=JMSUser::class)
     * )
     */
    #[Route(path: '/api/jms', methods: ['GET'])]
    public function userAction()
    {
    }

    /**
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=VirtualProperty::class)
     * )
     */
    #[Route(path: '/api/yaml', methods: ['GET'])]
    public function yamlAction()
    {
    }

    /**
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=JMSComplex::class, groups={"list", "details", "User" : {"list"}})
     * )
     */
    #[Route(path: '/api/jms_complex', methods: ['GET'])]
    public function complexAction()
    {
    }

    /**
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=JMSDualComplex::class, groups={"Default", "complex" : {"User" : {"details"}}})
     * )
     */
    #[Route(path: '/api/jms_complex_dual', methods: ['GET'])]
    public function complexDualAction()
    {
    }

    /**
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=JMSNamingStrategyConstraints::class, groups={"Default"})
     * )
     */
    #[Route(path: '/api/jms_naming_strategy', methods: ['GET'])]
    public function namingStrategyConstraintsAction()
    {
    }

    /**
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=JMSChat::class, groups={"Default", "members" : {"mini"}})
     * )
     */
    #[Route(path: '/api/jms_chat', methods: ['GET'])]
    public function chatAction()
    {
    }

    /**
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=JMSPicture::class, groups={"mini"})
     * )
     */
    #[Route(path: '/api/jms_picture', methods: ['GET'])]
    public function pictureAction()
    {
    }

    /**
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=JMSChatUser::class, groups={"mini"})
     * )
     */
    #[Route(path: '/api/jms_mini_user', methods: ['GET'])]
    public function minUserAction()
    {
    }

    /**
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *     @Model(type=JMSChatRoomUser::class, groups={"mini", "friend": {"living":{"Default"}}})
     * )
     */
    #[Route(path: '/api/jms_mini_user_nested', methods: ['GET'])]
    public function minUserNestedAction()
    {
    }
}
