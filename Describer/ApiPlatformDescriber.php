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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ApiPlatformDescriber extends ExternalDocDescriber
{
    public function __construct(Documentation $documentation, DocumentationNormalizer $normalizer, UrlGeneratorInterface $urlGenerator)
    {
        parent::__construct(function () use ($documentation, $normalizer, $urlGenerator) {
            $documentation = (array) $normalizer->normalize($documentation);
            unset($documentation['basePath']);

            return $documentation;
        });
    }
}
