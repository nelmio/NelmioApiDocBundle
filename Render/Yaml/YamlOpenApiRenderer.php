<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Render\Yaml;

use Nelmio\ApiDocBundle\Render\OpenApiRenderer;
use Nelmio\ApiDocBundle\Render\RenderOpenApi;
use OpenApi\Annotations\OpenApi;

/**
 * @internal
 */
class YamlOpenApiRenderer implements OpenApiRenderer
{
    public function getFormat(): string
    {
        return RenderOpenApi::YAML;
    }

    public function render(OpenApi $spec, array $options = []): string
    {
        return $spec->toYaml();
    }
}
