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

use EXSyst\Component\Swagger\Operation;
use EXSyst\Component\Swagger\Swagger;
use Symfony\Component\Routing\Route;

/**
 * @internal
 */
trait RouteDescriberTrait
{
    /**
     * @internal
     *
     * @return Operation[]
     */
    private function getOperations(Swagger $api, Route $route): array
    {
        $operations = [];
        $path = $api->getPaths()->get($this->normalizePath($route->getPath()));
        $methods = $route->getMethods() ?: Swagger::$METHODS;
        foreach ($methods as $method) {
            $method = strtolower($method);
            if (!in_array($method, Swagger::$METHODS)) {
                continue;
            }

            $operations[] = $path->getOperation($method);
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
