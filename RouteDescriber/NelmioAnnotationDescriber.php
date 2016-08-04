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

use Doctrine\Common\Annotations\Reader;
use EXSyst\Component\Swagger\Parameter;
use EXSyst\Component\Swagger\Swagger;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Route;

class NelmioAnnotationDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    private $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public function describe(Swagger $api, Route $route, \ReflectionMethod $reflectionMethod)
    {
        $annotation = $this->annotationReader->getMethodAnnotation($reflectionMethod, ApiDoc::class);
        if (null === $annotation) {
            return;
        }

        // some fields aren't available otherwise
        $annotationArray = $annotation->toArray();

        foreach ($this->getOperations($api, $route) as $operation) {
            if (null === $operation->getDescription()) {
                $operation->setDescription($annotation->getDescription());
            }
            if (null === $operation->getDeprecated() && $annotation->getDeprecated()) {
                $operation->setDeprecated(true);
            }

            // Request parameters
            foreach ($annotation->getParameters() as $name => $configuration) {
                $parameter = $operation->getParameters()->get($name, 'formData');
                if (isset($configuration['required']) && $configuration['required']) {
                    $parameter->setRequired(true);
                }

                $this->configureParameter($parameter, $configuration);
            }

            // Query/Path required parameters
            $compiledRoute = $route->compile();
            $pathVariables = $compiledRoute->getVariables();
            $hostVariables = $compiledRoute->getHostVariables();
            foreach ($annotation->getRequirements() as $name => $configuration) {
                if (in_array($name, $pathVariables)) {
                    $in = 'path';
                } elseif (!in_array($name, $hostVariables)) {
                    $in = 'query';
                } else { // Host variables not supported
                    continue;
                }
                $parameter = $operation->getParameters()->get($name, $in);
                $parameter->setRequired(true);

                $this->configureParameter($parameter, $configuration);
            }
            // Optional Query parameters
            foreach ($annotation->getFilters() as $name => $configuration) {
                $parameter = $operation->getParameters()->get($name, 'query');
                $this->configureParameter($parameter, $configuration);
            }

            // External docs
            if (isset($annotationArray['link'])) {
                $operation->getExternalDocs()->setUrl($annotationArray['link']);
            }

            // Responses
            if (isset($annotationArray['statusCodes'])) {
                $responses = $operation->getResponses();
                foreach ($annotationArray['statusCodes'] as $statusCode => $description) {
                    $response = $responses->get($statusCode);
                    $response->setDescription($description);
                }
            }
        }
    }

    private function configureParameter(Parameter $parameter, array $configuration)
    {
        $dataType = null;
        if (isset($configuration['dataType'])) {
            $dataType = $configuration['dataType'];
        } elseif ($configuration['requirement']) {
            $dataType = $configuration['requirement'];
        }

        if ('[]' === substr($requirement, -2)) {
            $parameter->setType('array');
            $items = $parameter;
            do {
                $items->setCollectionFormat('multi');
                $requirement = substr($requirement, 0, -2);

                $items = $items->getItems();
            } while ('[]' === substr($requirement, -2));

            $items->setType('string');
            $items->setFormat($requirement);
        } else {
            $parameter->setType('string');
            $parameter->setFormat($requirement);
        }

        if (isset($configuration['description'])) {
            $parameter->setDescription($configuration['description']);
        }
        if (isset($configuration['default'])) {
            $parameter->setDefault($configuration['default']);
        }
    }
}
