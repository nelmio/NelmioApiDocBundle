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
use Symfony\Component\Routing\Route;

final class RouteMetadataDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    public function describe(OpenApi $api, Route $route, \ReflectionMethod $reflectionMethod): void
    {
        foreach ($this->getOperations($api, $route) as $operation) {
            $operation->security = $route->getSchemes() ?: null;

            $requirements = $route->getRequirements();
            $compiledRoute = $route->compile();

            // Don't include host requirements
            foreach ($compiledRoute->getPathVariables() as $pathVariable) {
                if ('_format' === $pathVariable) {
                    continue;
                }

                $parameter = Util::getOperationParameter($operation, $pathVariable, 'path');
                $parameter->required = true;

                if (null === $parameter->schema) {
                    $parameter->type = 'string'; // @todo
                }

                if (isset($requirements[$pathVariable])) {
                    $parameter->pattern = $requirements[$pathVariable]; // @todo
                }
            }
        }
    }
}
