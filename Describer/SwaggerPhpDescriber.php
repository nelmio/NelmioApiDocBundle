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

use Doctrine\Common\Annotations\Reader;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\SwaggerPhp\ModelRegister;
use Nelmio\ApiDocBundle\SwaggerPhp\Util;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Processors\MergeJsonContent;
use OpenApi\Processors\MergeXmlContent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class SwaggerPhpDescriber implements ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    private $routeCollection;
    private $controllerReflector;
    private $annotationReader;
    private $logger;
    private $overwrite;

    public function __construct(RouteCollection $routeCollection, ControllerReflector $controllerReflector, Reader $annotationReader, LoggerInterface $logger)
    {
        $this->routeCollection = $routeCollection;
        $this->controllerReflector = $controllerReflector;
        $this->annotationReader = $annotationReader;
        $this->logger = $logger;
    }

    public function describe(OA\OpenApi $api): void
    {
        $analysis = $this->getAnnotations($api);
        $analysis->process($this->getProcessors());
        try {
            $analysis->validate();
        } catch (\Throwable $e) {
//            var_dump($api);
            throw $e;
        }
    }

    private function getProcessors(): array
    {
//        $defaultProcessors = [];
//        $processors = [];
//
//        foreach (Analysis::processors() as $processor) {
//            if ($processor instanceof MergeJsonContent || $processor instanceof MergeXmlContent) {
//                $processors[] = $processor;
//            } else {
//                $defaultProcessors[] = $processor;
//            }
//        }
//
//        $processors[] = new ModelRegister($this->modelRegistry);
//
//        return array_merge($processors, $defaultProcessors);

        $processors = [
            new ModelRegister($this->modelRegistry),
        ];

        return array_merge($processors, Analysis::processors());

//        Analysis::registerProcessor(new ModelRegister($this->modelRegistry));
//
//        var_dump(count(Analysis::processors()));
//
//        return Analysis::processors();
    }

    private function getAnnotations(OA\OpenApi $api): Analysis
    {
        $analysis = new Analysis();
        $analysis->openapi = $api;

        $classAnnotations = [];

        /** @var \ReflectionMethod $method */
        foreach ($this->getMethodsToParse() as $method => list($path, $httpMethods)) {
            $declaringClass = $method->getDeclaringClass();
            $declaringClassName = $declaringClass->getName();

            if (!array_key_exists($declaringClassName, $classAnnotations)) {
                $classAnnotations = array_filter($this->annotationReader->getClassAnnotations($declaringClass), function ($v) {
                    return $v instanceof OA\AbstractAnnotation;
                });
                $classAnnotations[$declaringClassName] = $classAnnotations;
            }

            $annotations = array_filter($this->annotationReader->getMethodAnnotations($method), function ($v) {
                return $v instanceof OA\AbstractAnnotation;
            });

            if (0 === count($annotations)) {
                continue;
            }

            $path = Util::getPath($api, $path);
            $path->_context->namespace = $method->getNamespaceName();
            $path->_context->class = $declaringClass->getShortName();
            $path->_context->method = $method->name;
            $path->_context->filename = $method->getFileName();

            $nestedContext = Util::createContext(['nested' => $path], $path->_context);
            $implicitAnnotations = [];
            $mergeProperties = new \stdClass();

            foreach (array_merge($annotations, $classAnnotations[$declaringClass->getName()]) as $annotation) {
                $annotation->_context = $nestedContext;

                if ($annotation instanceof Operation) {
                    foreach ($httpMethods as $httpMethod) {
                        $operation = Util::getOperation($path, $httpMethod);
                        $operation->mergeProperties($annotation);
                    }

                    continue;
                }

                if ($annotation instanceof OA\Operation) {
                    $operation = Util::getOperation($path, $annotation->method);
                    $operation->mergeProperties($annotation);

                    continue;
                }

                if ($annotation instanceof Security) {
                    $annotation->validate();
                    $mergeProperties->security[] = [$annotation->name => []];

                    continue;
                }

                if ($annotation instanceof OA\Tag) {
                    $annotation->validate();
                    $mergeProperties->tags[] = $annotation->name;

                    continue;
                }

                if (!$annotation instanceof OA\Response && !$annotation instanceof OA\Parameter && !$annotation instanceof OA\ExternalDocumentation) {
                    throw new \LogicException(sprintf('Using the annotation "%s" as a root annotation in "%s::%s()" is not allowed.', get_class($annotation), $method->getDeclaringClass()->name, $method->name));
                }

                $implicitAnnotations[] = $annotation;
            }

            if (empty($implicitAnnotations) && empty(get_object_vars($mergeProperties))) {
                continue;
            }

            // Registers new annotations
            $analysis->addAnnotations($implicitAnnotations, null);

            foreach ($httpMethods as $httpMethod) {
                $operation = Util::getOperation($path, $httpMethod);
                $operation->merge($implicitAnnotations);
                $operation->mergeProperties($mergeProperties);
            }
        }

        return $analysis;
    }

    private function getMethodsToParse(): \Generator
    {
        foreach ($this->routeCollection->all() as $route) {
            if (!$route->hasDefault('_controller')) {
                continue;
            }

            $controller = $route->getDefault('_controller');
            $reflectedMethod = $this->controllerReflector->getReflectionMethod($controller);

            if (null === $reflectedMethod) {
                continue;
            }

            $path = $this->normalizePath($route->getPath());

            $supportedHttpMethods = $this->getSupportedHttpMethods($route);

            if (empty($supportedHttpMethods)) {
                $this->logger->warning('None of the HTTP methods specified for path {path} are supported by swagger-ui, skipping this path', [
                    'path' => $path,
                ]);

                continue;
            }

            yield $reflectedMethod => [$path, $supportedHttpMethods];
        }
    }

    private function getSupportedHttpMethods(Route $route): array
    {
        $allMethods = Util::$operations;
        $methods = array_map('strtolower', $route->getMethods());

        return array_intersect($methods ?: $allMethods, $allMethods);
    }

    private function normalizePath(string $path): string
    {
        if ('.{_format}' === substr($path, -10)) {
            $path = substr($path, 0, -10);
        }

        return $path;
    }
}
