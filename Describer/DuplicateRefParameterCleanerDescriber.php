<?php

namespace Nelmio\ApiDocBundle\Describer;

use EXSyst\Component\Swagger\Operation;
use EXSyst\Component\Swagger\Parameter;
use EXSyst\Component\Swagger\Path;
use EXSyst\Component\Swagger\Swagger;

/**
 * Class DuplicateRefParameterCleanerDescriber
 *
 * Remove parameters that also have a ref version as they will be duplicated otherwise, resulting in a broken swagger UI
 */
final class DuplicateRefParameterCleanerDescriber implements DescriberInterface
{
    /**
     * Length of #/parameters/
     */
    const PARAMTERS_REF_LENGTH = 13;

    /**
     * @param Swagger $api
     */
    public function describe(Swagger $api)
    {
        /** @var Path $path */
        foreach ($api->getPaths() as $path) {
            /** @var Operation $operation */
            foreach ($path->getOperations() as $operation) {
                $parametersToRemove = [];
                /** @var Parameter $parameter */
                foreach ($operation->getParameters() as $parameter) {
                    if ($parameter->getRef()) {
                        $ref = substr($parameter->getRef(), self::PARAMTERS_REF_LENGTH);
                        if (isset($api->getParameters()[$ref])) {
                            /** @var Parameter $refParameter */
                            $refParameter = $api->getParameters()[$ref];
                            $refParameterId = sprintf('%s/%s', $refParameter->getName(), $refParameter->getIn());
                            if ($operation->getParameters()->has($refParameterId)) {
                                $parametersToRemove[] = $operation->getParameters()->get($refParameterId);
                            }
                        }
                    }
                }
                foreach ($parametersToRemove as $parameterToRemove) {
                    $operation->getParameters()->remove($parameterToRemove);
                }
            }
        }
    }
}
