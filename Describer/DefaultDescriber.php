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
use OpenApi\Annotations as OA;
use const OpenApi\UNDEFINED;

/**
 * Makes the swagger documentation valid even if there are missing fields.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
final class DefaultDescriber implements DescriberInterface
{
    public function describe(OA\OpenApi $api): void
    {
        /** @var OA\Info $info */
        $info = Util::getChild($api, OA\Info::class);
        if (null === $info->title) {
            $info->title = '';
        }
        if (null === $info->version) {
            $info->version = '0.0.0';
        }
        $paths = isset($api->paths) && UNDEFINED !== $api->paths ? $api->paths : [];
        foreach ($paths as $uri => $path) {
            foreach (Util::$operations as $method) {
                /** @var OA\Operation $operation */
                $operation = $path->{$method};
                if (UNDEFINED !== $operation && null !== $operation && empty($operation->responses ?? [])) {
                    /** @var OA\Response $response */
                    $response = Util::getIndexedCollectionItem($operation, OA\Response::class, 'default');
                    $response->description = '';
                }
            }
        }
    }
}
