<?php

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

class EntityThroughNameConverter
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var EntityThroughNameConverterNested
     */
    public $nested;
}
