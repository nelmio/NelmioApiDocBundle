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
use OpenApi\Annotations\Server;

class JsonOpenApiRenderer implements OpenApiRenderer
{
    public function getFormat(): string
    {
        return RenderOpenApi::JSON;
    }

    public function render(OpenApi $spec, array $options = []): string
    {
        $options += [
            'server_url' => null,
            'no-pretty' => false,
        ];
        $flags = $options['no-pretty'] ? 0 : JSON_PRETTY_PRINT;

        if ($options['server_url']) {
            $spec->servers = [new Server(['url' => $options['server_url']])];
        }

        return json_encode($spec, $flags);
    }
}
