<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity\Issue2218;

use Symfony\Component\Serializer\Annotation as Serializer;

class EventOutput
{
    #[Serializer\Groups([SerializationGroup::EventOutputAll])]
    private readonly Event $event;

    #[Serializer\Groups([SerializationGroup::EventOutputAll])]
    private readonly Member $member;

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getMember(): Member
    {
        return $this->member;
    }
}
