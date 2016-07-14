<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\RouteDescriber;

use gossi\swagger\Operation;
use gossi\swagger\Swagger;
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
    private function getOperations(Swagger $api, Route $route)
    {
        $path = $api->getPaths()->get($route->getPath());
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
}
