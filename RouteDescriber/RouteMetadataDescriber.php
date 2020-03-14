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
use EXSyst\Component\Swagger\Parameter;
use EXSyst\Component\Swagger\Swagger;
use LogicException;
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
            $existingParams = $this->getRefParams($api, $operation);

            // Don't include host requirements
            foreach ($compiledRoute->getPathVariables() as $pathVariable) {
                if ('_format' === $pathVariable) {
                    continue;
                }

                $paramId = $pathVariable.'/path';
                $parameter = $existingParams[$paramId] ?? null;
                if (null !== $parameter) {
                    if (!$parameter->getRequired()) {
                        throw new LogicException(\sprintf('Global parameter "%s" is used as part of route "%s" and must be set as "required"', $pathVariable, $route->getPath()));
                    }

                    continue;
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

    /**
     * The '$ref' parameters need special handling, since their objects are missing 'name' and 'in'.
     *
     * @return Parameter[] existing $ref parameters
     */
    private function getRefParams(Swagger $api, Operation $operation): array
    {
        /** @var Parameter[] $globalParams */
        $globalParams = $api->getParameters();
        $existingParams = [];

        foreach ($operation->getParameters() as $id => $parameter) {
            $ref = $parameter->getRef();
            if (null === $ref) {
                // we only concern ourselves with '$ref' parameters, so continue the loop
                continue;
            }

            $ref = \mb_substr($ref, 13); // trim the '#/parameters/' part of ref
            if (!isset($globalParams[$ref])) {
                // this shouldn't happen during proper configs, but in case of bad config, just ignore it here
                continue;
            }

            $refParameter = $globalParams[$ref];

            // param ids are in form {name}/{in}
            $existingParams[\sprintf('%s/%s', $refParameter->getName(), $refParameter->getIn())] = $refParameter;
        }

        return $existingParams;
    }
}
