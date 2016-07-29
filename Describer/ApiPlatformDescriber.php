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
use ApiPlatform\Core\Swagger\DocumentationNormalizer;

class ApiPlatformDescriber extends ExternalDocDescriber
{
    /**
     * @param string $projectPath
     */
    public function __construct(Documentation $documentation, DocumentationNormalizer $normalizer, bool $overwrite = false)
    {
        parent::__construct(function () use ($documentation, $normalizer) {
            var_dump($normalizer->normalize($documentation));
            return $normalizer->normalize($documentation);
        }, $overwrite);
    }
}
