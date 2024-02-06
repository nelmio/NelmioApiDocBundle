<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

class SymfonyMapQueryString
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $nullableName,
        public ArticleType81 $articleType81,
        public ?ArticleType81 $nullableArticleType81,
    ) {
    }
}
