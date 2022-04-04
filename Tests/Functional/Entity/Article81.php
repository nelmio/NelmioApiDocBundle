<?php

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

class Article81
{
    public function __construct(
        public readonly int $id,
        public readonly ArticleType81 $type,
    ) {
    }
}
