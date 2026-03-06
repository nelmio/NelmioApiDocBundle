<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\EntityExcluded\Symfony7;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Attribute\SerializedPath;

class SerializedPathEntity
{
    #[SerializedPath('[data][name]')]
    public string $name;

    #[SerializedPath('[data][nickname]')]
    public ?string $nickname;

    /** @var list<string> */
    #[SerializedPath('[data][tags]')]
    public array $tags;

    #[SerializedPath('[data][nested]')]
    public SerializedPathNestedEntity $nested;

    public function __construct(
        #[SerializedPath('[data][promoted]')]
        public int $promotedField,
    ) {
    }

    #[SerializedName('renamed')]
    public string $email;
}
