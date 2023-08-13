<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

class SymfonyMapQueryString
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $nullableName,
        public Article81 $article81Enum,
    ) {
    }
}
