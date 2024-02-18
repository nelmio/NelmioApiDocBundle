<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Controller\Issue2218;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Issue2218\ContactOutput;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Issue2218\SerializationGroup;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Attribute\Route;

class ContactController2218
{
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ContactOutput::class, groups: [
                SerializationGroup::ContactOutputAll,
                SerializationGroup::ContactAll,
                SerializationGroup::MemberAll
            ]))
        )
    )]
    #[Route('/contact/list', methods: ['GET'])]
    public function list(){}

    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            ref: new Model(type: ContactOutput::class, groups: [
                SerializationGroup::ContactOutputAll,
                SerializationGroup::ContactAll,
                SerializationGroup::MemberAll
            ])
        )
    )]
    #[Route('/contact/show', methods: ['GET'])]
    public function show(){}

    #[OA\Response(
        response: 201,
        description: 'Success',
        content: new OA\JsonContent(
            ref: new Model(type: ContactOutput::class, groups: [
                SerializationGroup::ContactOutputAll,
                SerializationGroup::ContactAll,
                SerializationGroup::MemberAll
            ])
        )
    )]
    #[Route('/contact/create', methods: ['GET'])]
    public function create(){}

    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            ref: new Model(type: ContactOutput::class, groups: [
                SerializationGroup::ContactOutputAll,
                SerializationGroup::ContactAll,
                SerializationGroup::MemberAll
            ])
        )
    )]
    #[Route('/contact/update', methods: ['GET'])]
    public function update(){}
}
