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

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Generator;

/**
 * Makes the swagger documentation valid even if there are missing fields.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
final class DefaultDescriber implements DescriberInterface
{
    public function describe(OA\OpenApi $api): void
    {
        // Info
        /** @var OA\Info $info */
        $info = Util::getChild($api, OA\Info::class);
        if (Generator::UNDEFINED === $info->title) {
            $info->title = '';
        }
        if (Generator::UNDEFINED === $info->version) {
            $info->version = '0.0.0';
        }

        // Paths
        if (Generator::UNDEFINED === $api->paths) {
            $api->paths = [];
        }
        foreach ($api->paths as $path) {
            foreach (Util::OPERATIONS as $method) {
                /** @var OA\Operation $operation */
                $operation = $path->{$method};
                if (Generator::UNDEFINED !== $operation && null !== $operation && (Generator::UNDEFINED === $operation->responses || [] === $operation->responses)) {
                    /** @var OA\Response $response */
                    $response = Util::getIndexedCollectionItem($operation, OA\Response::class, 'default');
                    $response->description = '';
                }
            }
        }
    }
}
