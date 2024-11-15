<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Render\Json;

use Nelmio\ApiDocBundle\Render\OpenApiRenderer;
use Nelmio\ApiDocBundle\Render\RenderOpenApi;
use OpenApi\Annotations\OpenApi;

/**
 * @internal
 */
class JsonOpenApiRenderer implements OpenApiRenderer
{
    public function getFormat(): string
    {
        return RenderOpenApi::JSON;
    }

    public function render(OpenApi $spec, array $options = []): string
    {
        $options += [
            'no-pretty' => false,
        ];
        $flags = true === $options['no-pretty'] ? 0 : \JSON_PRETTY_PRINT;

        return json_encode($spec, $flags | \JSON_UNESCAPED_SLASHES);
    }
}
