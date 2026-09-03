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

use Symfony\Component\Serializer\Attribute\SerializedPath;

class SerializedPathInDeepEntity
{
    #[SerializedPath('[response][data][name]')]
    public string $deepName;

    #[SerializedPath('[meta][count]')]
    public int $count;

    #[SerializedPath('[status]')]
    public string $currentStatus;

    #[SerializedPath('[meta][label]')]
    public function setLabel(string $label): void
    {
    }

    #[SerializedPath('[data][data][value]')]
    public string $duplicatedSegment;
}
