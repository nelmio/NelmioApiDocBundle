<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Describer;

use EXSyst\Component\Swagger\Collections\Parameters;
use EXSyst\Component\Swagger\Operation;
use EXSyst\Component\Swagger\Parameter;
use EXSyst\Component\Swagger\Path;
use EXSyst\Component\Swagger\Swagger;

/**
 * Merges parameters that have been added as refs through config files with those that get auto generated based on
 * routes.
 */
class ParameterRefMergeDescriber implements DescriberInterface
{
    /**
     * {@inheritdoc}
     */
    public function describe(Swagger $api)
    {
        /** @var Path $path */
        foreach ($api->getPaths() as $path) {
            /** @var Operation $operation */
            foreach ($path->getOperations() as $operation) {
                $this->checkOperation($api, $operation);
            }
        }
    }

    /**
     * This method removes parameters that also have a ref version as they will be duplicated otherwise.
     */
    private function checkOperation(Swagger $api, Operation $operation)
    {
        $parametersToRemove = [];

        /** @var Parameter[] $globalParams */
        $globalParams = $api->getParameters();
        /** @var Parameters|Parameter[] $currentParams */
        $currentParams = $operation->getParameters();

        foreach ($currentParams as $parameter) {
            $ref = $parameter->getRef();

            if (null === $ref) {
                // we only concern ourselves with '$ref' parameters
                continue;
            }

            $ref = \mb_substr($ref, 13); // trim the '#/parameters/' part of ref
            if (!isset($globalParams[$ref])) {
                // this really shouldn't happen, if it does there will be other failures elsewhere, so just ignore here
                continue;
            }

            $refParameter = $globalParams[$ref];
            // param ids are in form {name}/{in}
            $refParameterId = \sprintf('%s/%s', $refParameter->getName(), $refParameter->getIn());
            if ($currentParams->has($refParameterId)) {
                // if we got here it means a duplicate parameter is directly defined, schedule it for removal
                $parametersToRemove[] = $currentParams->get($refParameterId);
            }
        }

        foreach ($parametersToRemove as $parameterToRemove) {
            $currentParams->remove($parameterToRemove);
        }
    }
}
