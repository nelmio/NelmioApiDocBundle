<?php

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

use Symfony\Component\Uid\Uuid;

class EntityWithUuid
{
    public ?Uuid $id;
    private string $name;

    public function __construct(string $name)
    {
        $this->id = Uuid::v1();
        $this->name = $name;
    }
}
