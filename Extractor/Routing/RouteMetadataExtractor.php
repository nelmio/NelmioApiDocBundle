<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\Extractor\Routing;

use gossi\swagger\Swagger;
use Symfony\Component\Routing\Route;

class RouteMetadataExtractor implements RouteExtractorInterface
{
    use RouteExtractorTrait;

    public function extractIn(Swagger $api, Route $route, \ReflectionMethod $reflectionMethod)
    {
        foreach ($this->getOperations($api, $route) as $operation) {
            $operation->getSchemes()->addAll($route->getSchemes());

            foreach ($route->getRequirements() as $parameterName => $requirement) {
                $parameter = $operation->getParameters()->get($parameterName, 'path');
                $parameter->setRequired(true);
                $parameter->setType(Swagger::T_STRING);
                $parameter->setFormat($requirement);
            }
        }
    }
}
