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

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(type="object")
 */
class EntityWithObjectType
{
    /**
     * @var string
     */
    public $notIgnored = 'this should be read';
}
