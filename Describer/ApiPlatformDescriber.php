<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\Describer;

use ApiPlatform\Core\Documentation\Documentation;
use ApiPlatform\Core\Swagger\Serializer\DocumentationNormalizer;

class ApiPlatformDescriber extends ExternalDocDescriber
{
    public function __construct(Documentation $documentation, DocumentationNormalizer $normalizer, bool $overwrite = false)
    {
        parent::__construct(function () use ($documentation, $normalizer) {
            return (array) $normalizer->normalize($documentation);
        }, $overwrite);
    }
}
