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

use LogicException;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\Routing\Route;

/**
 * Should be last route describer executed to make sure all params are set.
 */
final class RouteMetadataDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    public function describe(OA\OpenApi $api, Route $route, \ReflectionMethod $reflectionMethod)
    {
        foreach ($this->getOperations($api, $route) as $operation) {
            $requirements = $route->getRequirements();
            $compiledRoute = $route->compile();
            $existingParams = $this->getRefParams($api, $operation);

            // Don't include host requirements
            foreach ($compiledRoute->getPathVariables() as $pathVariable) {
                if ('_format' === $pathVariable) {
                    continue;
                }

                $paramId = $pathVariable.'/path';
                /** @var OA\Parameter $parameter */
                $parameter = $existingParams[$paramId] ?? null;
                if (null !== $parameter) {
                    if (!$parameter->required || Generator::UNDEFINED === $parameter->required) {
                        throw new LogicException(\sprintf('Global parameter "%s" is used as part of route "%s" and must be set as "required"', $pathVariable, $route->getPath()));
                    }

                    continue;
                }

                $parameter = Util::getOperationParameter($operation, $pathVariable, 'path');
                $parameter->required = true;

                $parameter->schema = Util::getChild($parameter, OA\Schema::class);

                if (Generator::UNDEFINED === $parameter->schema->type) {
                    $parameter->schema->type = 'string';
                }

                if (isset($requirements[$pathVariable]) && Generator::UNDEFINED === $parameter->schema->pattern) {
                    $parameter->schema->pattern = $requirements[$pathVariable];
                }
            }
        }
    }

    /**
     * The '$ref' parameters need special handling, since their objects are missing 'name' and 'in'.
     *
     * @return OA\Parameter[] existing $ref parameters
     */
    private function getRefParams(OA\OpenApi $api, OA\Operation $operation): array
    {
        /** @var OA\Parameter[] $globalParams */
        $globalParams = Generator::UNDEFINED !== $api->components && Generator::UNDEFINED !== $api->components->parameters ? $api->components->parameters : [];
        $globalParams = array_column($globalParams, null, 'parameter'); // update the indexes of the array with the reference names actually used
        $existingParams = [];

        $operationParameters = Generator::UNDEFINED !== $operation->parameters ? $operation->parameters : [];
        /** @var OA\Parameter $parameter */
        foreach ($operationParameters as $id => $parameter) {
            $ref = $parameter->ref;
            if (Generator::UNDEFINED === $ref) {
                // we only concern ourselves with '$ref' parameters, so continue the loop
                continue;
            }

            $ref = \mb_substr($ref, 24); // trim the '#/components/parameters/' part of ref
            if (!isset($globalParams[$ref])) {
                // this shouldn't happen during proper configs, but in case of bad config, just ignore it here
                continue;
            }

            $refParameter = $globalParams[$ref];

            // param ids are in form {name}/{in}
            $existingParams[\sprintf('%s/%s', $refParameter->name, $refParameter->in)] = $refParameter;
        }

        return $existingParams;
    }
}
