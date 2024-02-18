<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity\Issue2218;

use Symfony\Component\Serializer\Annotation as Serializer;

class Member
{
    #[Serializer\Groups([SerializationGroup::MemberAll])]
    public ?int $id = null;
}
