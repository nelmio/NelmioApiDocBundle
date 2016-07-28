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

use gossi\swagger\Swagger;
use Symfony\Component\Routing\Route;

class RouteMetadataDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    public function describe(Swagger $api, Route $route, \ReflectionMethod $reflectionMethod)
    {
        foreach ($this->getOperations($api, $route) as $operation) {
            $operation->getSchemes()->addAll($route->getSchemes());

            foreach ($route->getRequirements() as $parameterName => $requirement) {
                $parameter = $operation->getParameters()->get($parameterName, 'path');
                $parameter->setRequired(true);
                $parameter->setType('string');
                $parameter->setFormat($requirement);
            }
        }
    }
}
