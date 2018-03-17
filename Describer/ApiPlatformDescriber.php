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
use ApiPlatform\Core\Swagger\Serializer\ApiGatewayNormalizer;
use ApiPlatform\Core\Swagger\Serializer\DocumentationNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ApiPlatformDescriber extends ExternalDocDescriber
{
    public function __construct(Documentation $documentation, $normalizer, UrlGeneratorInterface $urlGenerator)
    {
        if (!$normalizer instanceof ApiGatewayNormalizer && !$normalizer instanceof DocumentationNormalizer) {
            throw new \InvalidArgumentException(sprintf('Argument 2 passed to %s() must be an instance of %s or %s, %s given.', __METHOD__, ApiGatewayNormalizer::class, DocumentationNormalizer::class, get_class($normalizer)));
        }

        parent::__construct(function () use ($documentation, $normalizer, $urlGenerator) {
            $documentation = (array) $normalizer->normalize($documentation);
            unset($documentation['basePath']);

            return $documentation;
        });
    }
}
