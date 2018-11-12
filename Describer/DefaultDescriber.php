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

use Nelmio\ApiDocBundle\SwaggerPhp\Util;
use Swagger\Annotations\Info;
use Swagger\Annotations\Operation;
use Swagger\Annotations\Response;
use Swagger\Annotations\Swagger;

/**
 * Makes the swagger documentation valid even if there are missing fields.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
final class DefaultDescriber implements DescriberInterface
{
    public function describe(Swagger $api)
    {
        /** @var Info $info */
        $info = Util::getChild($api, Info::class);
        if (null === $info->title) {
            $info->title = '';
        }
        if (null === $info->version) {
            $info->version = '0.0.0';
        }
        $paths = $api->paths ?? [];
        foreach ($paths as $uri => $path) {
            foreach (Util::$operations as $method) {
                /** @var Operation $operation */
                $operation = $path->{$method};
                if (null !== $operation && empty($operation->responses ?? [])) {
                    /** @var Response $response */
                    $response = Util::getIndexedCollectionItem($operation, Response::class, 'default');
                    $response->description = '';
                }
            }
        }
    }
}
