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

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedPath;

class SerializedPathWithGroupsEntity
{
    #[Groups(['read'])]
    #[SerializedPath('[data][name]')]
    public string $name;

    #[Groups(['admin'])]
    #[SerializedPath('[data][secret]')]
    public string $secret;

    #[Groups(['read', 'admin'])]
    #[SerializedPath('[data][id]')]
    public int $identifier;

    #[Groups(['read'])]
    #[SerializedPath('[meta][version]')]
    public string $version;

    #[Groups(['read'])]
    public string $type;
}
