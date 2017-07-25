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
use Symfony\Component\Routing\RequestContext;

final class ApiPlatformDescriber extends ExternalDocDescriber
{
    public function __construct(Documentation $documentation, DocumentationNormalizer $normalizer, UrlGeneratorInterface $urlGenerator)
    {
        parent::__construct(function () use ($documentation, $normalizer, $urlGenerator) {
            $baseContext = $urlGenerator->getContext();
            $urlGenerator->setContext(new RequestContext());
            try {
                $basePath = $urlGenerator->generate('api_entrypoint');
            } finally {
                $urlGenerator->setContext($baseContext);
            }

            $documentation = (array) $normalizer->normalize($documentation);
            unset($documentation['basePath']);

            foreach ($documentation['paths'] as $path => $value) {
                $paths['/'.ltrim($basePath.'/'.ltrim($path, '/'), '/')] = $value;
            }

            $documentation['paths'] = $paths;

            return $documentation;
        });
    }
}
