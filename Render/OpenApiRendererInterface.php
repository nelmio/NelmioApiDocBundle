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
use Symfony\Component\HttpFoundation\Response;

interface OpenApiRendererInterface
{
    public static function getFormat(): string;

    public function __invoke(OpenApi $spec, array $options = []): Response;
}
