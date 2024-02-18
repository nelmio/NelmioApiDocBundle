<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity\Issue2218;

use Symfony\Component\Serializer\Annotation as Serializer;

class ContactOutput
{
    #[Serializer\Groups([SerializationGroup::ContactOutputAll])]
    private readonly Contact $contact;

    #[Serializer\Groups([SerializationGroup::ContactOutputAll])]
    private readonly Member $member;

    public function getContact(): Contact
    {
        return $this->contact;
    }

    public function getMember(): Member
    {
        return $this->member;
    }
}
