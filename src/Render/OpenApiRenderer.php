<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Render;

use OpenApi\Annotations\OpenApi;

/**
 * @internal
 */
interface OpenApiRenderer
{
    public function getFormat(): string;

    /**
     * @param array<string, mixed> $options
     */
    public function render(OpenApi $spec, array $options = []): string;
}
