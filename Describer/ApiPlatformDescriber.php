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

use ApiPlatform\Core\Swagger\Serializer\DocumentationNormalizer;
use ApiPlatform\Documentation\DocumentationInterface;
use ApiPlatform\OpenApi\OpenApi;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ApiPlatformDescriber extends ExternalDocDescriber
{
    public function __construct($documentation, NormalizerInterface $normalizer)
    {
        if (false === $documentation instanceof DocumentationInterface && false === $documentation instanceof OpenApi) {
            throw new \InvalidArgumentException(sprintf('First parameter $documentation MUST be of type "%s" or "%s".', DocumentationInterface::class, OpenApi::class));
        }

        if (!$normalizer->supportsNormalization($documentation, 'json')) {
            throw new \InvalidArgumentException(sprintf('Argument 2 passed to %s() must implement %s and support normalization of %s. The normalizer provided is an instance of %s.', __METHOD__, NormalizerInterface::class, Documentation::class, get_class($normalizer)));
        }

        parent::__construct(function () use ($documentation, $normalizer) {

            $documentation = (array) $normalizer->normalize(
                $documentation,
                null,
                class_exists(DocumentationNormalizer::class) ? [DocumentationNormalizer::SPEC_VERSION => 3] : []
            );

            // TODO: remove this
            // Temporary fix: zircote/swagger-php does no longer support 3.0.x with x > 0
            unset($documentation['openapi']);
            unset($documentation['basePath']);
            unset($documentation['servers']);

            return $documentation;
        });
    }
}
