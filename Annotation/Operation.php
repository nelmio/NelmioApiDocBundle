<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Annotation;

use OpenApi\Annotations\Operation as BaseOperation;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Operation extends BaseOperation
{
}
