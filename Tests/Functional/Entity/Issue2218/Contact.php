<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity\Issue2218;

use Symfony\Component\Serializer\Annotation as Serializer;

class Contact
{
    #[Serializer\Groups([SerializationGroup::ContactAll])]
    public ?int $id = null;

    #[Serializer\Ignore]
    private Member $member;

    public function getMember(): Member
    {
        return $this->member;
    }
}
