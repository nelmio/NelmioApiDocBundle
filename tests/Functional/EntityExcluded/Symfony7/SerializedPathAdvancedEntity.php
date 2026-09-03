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

use Nelmio\ApiDocBundle\Attribute\Ignore;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\SerializedPath;

class SerializedPathAdvancedEntity
{
    #[SerializedPath('[data][name]')]
    #[OA\Property(description: 'The user name', example: 'John')]
    public string $name;

    #[SerializedPath('[data][status]')]
    public string $status = 'active';

    #[SerializedPath('[response][result][items][count]')]
    public int $deepCount;

    #[SerializedPath('[optional][first]')]
    public ?string $first;

    #[Ignore]
    #[SerializedPath('[hidden][value]')]
    public string $ignoredField;
}
