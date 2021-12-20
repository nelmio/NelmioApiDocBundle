<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\ControllerPhp81;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\JMSComplex;
use Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\JMSDualComplex;
use Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\JMSNamingStrategyConstraints;
use Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\JMSUser;
use Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\NestedGroup\JMSChat;
use Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\NestedGroup\JMSChatRoomUser;
use Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\NestedGroup\JMSChatUser;
use Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\NestedGroup\JMSPicture;
use Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\VirtualProperty;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

#[Route(host: 'api.example.com')]
class JMSController
{
    #[Route('/api/jms', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Success',
        properties: ['value' => new Model(type: JMSUser::class)],
    )]
    public function userAction()
    {
    }

    #[Route('/api/yaml', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Success',
        properties: ['value' => new Model(type: VirtualProperty::class)],
    )]
    public function yamlAction()
    {
    }

    #[Route('/api/jms_complex', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Success',
        properties: ['value' => new Model(type: JMSComplex::class, groups: ['list', 'details', 'User' => ['list']])],
    )]
    public function complexAction()
    {
    }

    #[Route('/api/jms_complex_dual', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Success',
        properties: ['value' => new Model(type: JMSDualComplex::class, groups: ['Default', 'complex', 'User' => ['details']])],
    )]
    public function complexDualAction()
    {
    }

    #[Route('/api/jms_naming_strategy', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Success',
        properties: ['value' => new Model(type: JMSNamingStrategyConstraints::class, groups: ['Default'])],
    )]
    public function namingStrategyConstraintsAction()
    {
    }

    #[Route('/api/jms_chat', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Success',
        properties: ['value' => new Model(type: JMSChat::class, groups: ['Default', 'members' => ['mini']])],
    )]
    public function chatAction()
    {
    }

    #[Route('/api/jms_picture', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Success',
        properties: ['value' => new Model(type: JMSPicture::class, groups: ['mini'])],
    )]
    public function pictureAction()
    {
    }

    #[Route('/api/jms_mini_user', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Success',
        properties: ['value' => new Model(type: JMSChatUser::class, groups: ['mini'])],
    )]
    public function minUserAction()
    {
    }

    #[Route('/api/jms_mini_user_nested', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Success',
        properties: ['value' => new Model(type: JMSChatRoomUser::class, groups: ['mini', 'friend' => ['living' => ['Default']]])],
    )]
    public function minUserNestedAction()
    {
    }
}
