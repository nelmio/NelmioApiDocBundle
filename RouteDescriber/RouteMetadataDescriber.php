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

use EXSyst\Component\Swagger\Swagger;
use Symfony\Component\Routing\Route;

final class RouteMetadataDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    public function describe(Swagger $api, Route $route, \ReflectionMethod $reflectionMethod)
    {
        foreach ($this->getOperations($api, $route) as $operation) {
            $operation->merge(['schemes' => $route->getSchemes()]);

            $requirements = $route->getRequirements();
            $compiledRoute = $route->compile();

            // Don't include host requirements
            foreach ($compiledRoute->getPathVariables() as $pathVariable) {
                if ('_format' === $pathVariable) {
                    continue;
                }

                $parameter = $operation->getParameters()->get($pathVariable, 'path');
                $parameter->setRequired(true);

                if (null === $parameter->getType()) {
                    $parameter->setType('string');
                }

                if (isset($requirements[$pathVariable])) {
                    $parameter->setFormat($requirements[$pathVariable]);
                }
            }
        }
    }
}
