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

use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class EntityWithSelfReferencingContext
{
    #[OA\Property(
        type: 'object',
        additionalProperties: new OA\AdditionalProperties(type: 'array', items: new OA\Items(type: 'string')),
    )]
    public ?array $permissions = null;

    #[Context([AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true])]
    public ?EntityWithSelfReferencingContext $parent = null;
}
