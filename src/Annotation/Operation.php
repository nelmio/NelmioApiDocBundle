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

trigger_deprecation('nelmio/api-doc-bundle', '4.32.3', 'The "%s" class is deprecated and will be removed in 5.0. Use the "\Nelmio\ApiDocBundle\Attribute\Operation" attribute instead.', Operation::class);

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Operation extends \Nelmio\ApiDocBundle\Attribute\Operation
{
}
