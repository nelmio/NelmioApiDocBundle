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

/**
 * Should be last route describer executed to make sure all params are set.
 */
final class RouteMetadataDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    public function describe(Swagger $api, Route $route, \ReflectionMethod $reflectionMethod)
    {
        foreach ($this->getOperations($api, $route) as $operation) {
            $operation->merge(['schemes' => $route->getSchemes()]);

            $requirements = $route->getRequirements();
            $compiledRoute = $route->compile();

            $globalParams = $api->getParameters();
            $existingParams = [];
            foreach ($operation->getParameters() as $id => $parameter) {
                $ref = $parameter->getRef();
                if (null === $ref) {
                    $existingParams[$id] = true;

                    // we only concern ourselves with '$ref' parameters
                    continue;
                }

                $ref = \mb_substr($ref, 13); // trim the '#/parameters/' part of ref
                if (!isset($globalParams[$ref])) {
                    // this shouldn't happen, so just ignore here
                    continue;
                }

                $refParameter = $globalParams[$ref];

                // param ids are in form {name}/{in}
                $existingParams[\sprintf('%s/%s', $refParameter->getName(), $refParameter->getIn())] = true;
            }

            // Don't include host requirements
            foreach ($compiledRoute->getPathVariables() as $pathVariable) {
                if ('_format' === $pathVariable) {
                    continue;
                }

                if (isset($existingParams[$pathVariable.'/path'])) {
                    continue; // ignore this param, it is already defined
                }

                $parameter = $operation->getParameters()->get($pathVariable, 'path');
                $parameter->setRequired(true);

                if (null === $parameter->getType()) {
                    $parameter->setType('string');
                }

                if (isset($requirements[$pathVariable]) && null === $parameter->getPattern()) {
                    $parameter->setPattern($requirements[$pathVariable]);
                }
            }
        }
    }
}
