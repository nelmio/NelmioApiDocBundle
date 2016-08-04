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

use EXSyst\Component\Swagger\Swagger;
use Symfony\Component\Routing\Route;

class RouteMetadataDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    public function describe(Swagger $api, Route $route, \ReflectionMethod $reflectionMethod)
    {
        foreach ($this->getOperations($api, $route) as $operation) {
            $operation->merge(['schemes' => $route->getSchemes()]);

            $requirements = $route->getRequirements();
            $compiledRoute = $route->compile();

            // Don't include path variables
            foreach ($compiledRoute->getPathVariables() as $pathVariable) {
                $parameter = $operation->getParameters()->get($pathVariable, 'path');
                $parameter->setRequired(true);
                $parameter->setType('string');

                if (isset($requirements[$pathVariable])) {
                    $parameter->setFormat($requirements[$pathVariable]);
                }
            }
        }
    }
}
