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
            return (array) $normalizer->normalize($documentation);
        }, $overwrite);
    }
}
