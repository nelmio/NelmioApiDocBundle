<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\RouteDescriber;

use Nelmio\ApiDocBundle\SwaggerPhp\Util;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Route;

/**
 * @internal
 */
trait RouteDescriberTrait
{
    /**
     * @internal
     *
     * @return OA\Operation[]
     */
    private function getOperations(OpenApi $api, Route $route): array
    {
        $operations = [];
        $path = Util::getPath($api, $this->normalizePath($route->getPath()));
        $methods = $route->getMethods() ?: Util::$operations;
        foreach ($methods as $method) {
            $method = strtolower($method);
            if (!in_array($method, Util::$operations)) {
                continue;
            }

            $operations[] = Util::getOperation($path, $method);
        }

        return $operations;
    }

    private function normalizePath(string $path): string
    {
        if ('.{_format}' === substr($path, -10)) {
            $path = substr($path, 0, -10);
        }

        return $path;
    }
}
