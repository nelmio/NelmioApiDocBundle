<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Controller\Issue2218;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Issue2218\EventOutput;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Issue2218\SerializationGroup;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Attribute\Route;

class EventController2218
{
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: EventOutput::class, groups: [
                SerializationGroup::EventOutputAll,
                SerializationGroup::EventAll,
                SerializationGroup::MemberAll
            ]))
        )
    )]
    #[Route('/event/list', methods: ['GET'])]
    public function list(){}

    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            ref: new Model(type: EventOutput::class, groups: [
                SerializationGroup::EventOutputAll,
                SerializationGroup::EventAll,
                SerializationGroup::MemberAll
            ])
        )
    )]
    #[Route('/event/show', methods: ['GET'])]
    public function show(){}

    #[OA\Response(
        response: 201,
        description: 'Success',
        content: new OA\JsonContent(
            ref: new Model(type: EventOutput::class, groups: [
                SerializationGroup::EventOutputAll,
                SerializationGroup::EventAll,
                SerializationGroup::MemberAll
            ])
        )
    )]
    #[Route('/event/create', methods: ['GET'])]
    public function create(){}

    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            ref: new Model(type: EventOutput::class, groups: [
                SerializationGroup::EventOutputAll,
                SerializationGroup::EventAll,
                SerializationGroup::MemberAll
            ])
        )
    )]
    #[Route('/event/update', methods: ['GET'])]
    public function update(){}
}
