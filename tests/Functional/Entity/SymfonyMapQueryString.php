<?php

declare(strict_types=1);

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
