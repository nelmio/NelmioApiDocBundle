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

use Doctrine\Common\Annotations\Reader;
use gossi\swagger\Parameter;
use gossi\swagger\Swagger;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Route;

class NelmioAnnotationExtractor implements RouteExtractorInterface
{
    use RouteExtractorTrait;

    private $annotationReader;
    private $nelmioLoaded;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
        $this->nelmioLoaded = class_exists(ApiDoc::class);
    }

    public function extractIn(Swagger $api, Route $route, \ReflectionMethod $reflectionMethod)
    {
        if (!$this->nelmioLoaded) {
            return;
        }

        $annotation = $this->annotationReader->getMethodAnnotation($reflectionMethod, ApiDoc::class);
        // some fields aren't available otherwise
        $annotationArray = $annotation->toArray();
        if (null === $annotation) {
            return;
        }

        foreach ($this->getOperations($api, $route) as $operation) {
            if ($annotation->getDescription()) {
                $operation->setDescription($annotation->getDescription());
            }
            if (null !== $annotation->getDeprecated()) {
                $operation->setDeprecated($operation->getDeprecated || $annotation->getDeprecated());
            }

            // Request parameters
            foreach ($annotation->getParameters() as $name => $configuration) {
                $parameter = $operation->getParameters()->get($name, 'formData');
                if (isset($configuration['required'])) {
                    $parameter->setRequired($parameter->getRequired() || $configuration['required']);
                }

                $this->configureParameter($parameter, $configuration);
            }

            // Query parameters
            foreach ($annotation->getRequirements() as $name => $configuration) {
                $parameter = $operation->getParameters()->get($name, 'query');
                $parameter->setRequired(true);

                $this->configureParameter($parameter, $configuration);
            }
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

            $items->setType(Swagger::T_STRING);
            $items->setFormat($requirement);
        } else {
            $parameter->setType(Swagger::T_STRING);
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
