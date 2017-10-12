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

use EXSyst\Component\Swagger\Swagger;

/**
 * Makes the swagger documentation valid even if there are missing fields.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
final class DefaultDescriber implements DescriberInterface
{
    public function describe(Swagger $api)
    {
        // Info
        $info = $api->getInfo();
        if (null === $info->getTitle()) {
            $info->setTitle('');
        }
        if (null === $info->getVersion()) {
            $info->setVersion('0.0.0');
        }

        // Paths
        $paths = $api->getPaths();
        foreach ($paths as $uri => $path) {
            foreach ($path->getMethods() as $method) {
                $operation = $path->getOperation($method);

                // Default Response
                if (0 === iterator_count($operation->getResponses())) {
                    $defaultResponse = $operation->getResponses()->get('default');
                    $defaultResponse->setDescription('');
                }
            }
        }
    }
}
