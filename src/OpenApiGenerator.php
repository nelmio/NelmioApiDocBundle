<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle;

use OpenApi\Generator;

/**
 * Extension of OpenApi\Generator to be able to inject processors with dependency injection.
 *
 * @internal
 */
final class OpenApiGenerator extends Generator
{
    public function addNelmioProcessor(callable $processor, ?string $before = null): void
    {
        if (null === $before) {
            $this->getProcessorPipeline()->add($processor);
        } else {
            $this->getProcessorPipeline()->insert($processor, $before);
        }
    }
}
