<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Describer;

use ApiPlatform\Core\Documentation\Documentation;
use ApiPlatform\Core\Swagger\Serializer\DocumentationNormalizer;

final class ApiPlatformDescriber extends ExternalDocDescriber
{
    public function __construct(Documentation $documentation, DocumentationNormalizer $normalizer, bool $overwrite = false)
    {
        parent::__construct(function () use ($documentation, $normalizer) {
            $documentation = (array) $normalizer->normalize($documentation);
            // Remove base path
            if (isset($documentation['basePath'])) {
                $paths = [];
                foreach ($documentation['paths'] as $path => $value) {
                    $paths['/'.ltrim($documentation['basePath'].'/'.ltrim($path, '/'), '/')] = $value;
                }

                unset($documentation['basePath']);
                $documentation['paths'] = $paths;
            }

            return $documentation;
        }, $overwrite);
    }
}
