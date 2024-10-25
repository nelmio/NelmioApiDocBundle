<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Attribute;

use OpenApi\Annotations\Operation as BaseOperation;

/**
 * @final
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Operation extends BaseOperation
{
}
