<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

class Article81
{
    public function __construct(
        public readonly int $id,
        public readonly ArticleType81 $type,
        public readonly ArticleType81IntBacked $intBackedType,
        public readonly ArticleType81NotBacked $notBackedType,
        public readonly ?ArticleType81 $nullableType,
    ) {
    }
}
