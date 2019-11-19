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
use EXSyst\Component\Swagger\Swagger;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\SwaggerPhp\AddDefaults;
use Nelmio\ApiDocBundle\SwaggerPhp\ModelRegister;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use Psr\Log\LoggerInterface;
use Swagger\Analysis;
use Swagger\Annotations\AbstractAnnotation;
use Swagger\Annotations as SWG;
use Swagger\Context;
use Symfony\Component\Routing\RouteCollection;

final class SwaggerPhpDescriber implements ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    private $routeCollection;
    private $controllerReflector;
    private $annotationReader;
    private $logger;
    private $overwrite;

    public function __construct(RouteCollection $routeCollection, ControllerReflector $controllerReflector, Reader $annotationReader, LoggerInterface $logger, bool $overwrite = false)
    {
        $this->routeCollection = $routeCollection;
        $this->controllerReflector = $controllerReflector;
        $this->annotationReader = $annotationReader;
        $this->logger = $logger;
        $this->overwrite = $overwrite;
    }

    public function describe(Swagger $api)
    {
        $analysis = $this->getAnnotations($api);

        $analysis->process($this->getProcessors());
        $analysis->validate();

        $api->merge(json_decode(json_encode($analysis->swagger), true), $this->overwrite);
    }

    private function getProcessors(): array
    {
        $processors = [
            new AddDefaults(),
            new ModelRegister($this->modelRegistry),
        ];

        return array_merge($processors, Analysis::processors());
    }

    private function getAnnotations(Swagger $api): Analysis
    {
        $analysis = new Analysis();
        $analysis->addAnnotation(new class($api) extends SWG\Swagger {
            private $api;

            public function __construct(Swagger $api)
            {
                $this->api = $api;
                parent::__construct([]);
            }

            /**
             * Support definitions from the config and reference to models.
             */
            public function ref($ref)
            {
                if (0 === strpos($ref, '#/definitions/') && $this->api->getDefinitions()->has(substr($ref, 14))) {
                    return;
                }
                if (0 === strpos($ref, '#/parameters/') && isset($this->api->getParameters()[substr($ref, 13)])) {
                    return;
                }
                if (0 === strpos($ref, '#/responses/') && $this->api->getResponses()->has(substr($ref, 12))) {
                    return;
                }

                parent::ref($ref);
            }
        }, null);

        $operationAnnotations = [
            'get' => SWG\Get::class,
            'post' => SWG\Post::class,
            'put' => SWG\Put::class,
            'patch' => SWG\Patch::class,
            'delete' => SWG\Delete::class,
            'options' => SWG\Options::class,
            'head' => SWG\Head::class,
        ];

        $classAnnotations = [];

        foreach ($this->getMethodsToParse() as $method => list($path, $httpMethods)) {
            $declaringClass = $method->getDeclaringClass();
            if (!array_key_exists($declaringClass->getName(), $classAnnotations)) {
                $classAnnotations = array_filter($this->annotationReader->getClassAnnotations($declaringClass), function ($v) {
                    return $v instanceof SWG\AbstractAnnotation;
                });
                $classAnnotations[$declaringClass->getName()] = $classAnnotations;
            }

            $annotations = array_filter($this->annotationReader->getMethodAnnotations($method), function ($v) {
                return $v instanceof SWG\AbstractAnnotation;
            });

            if (0 === count($annotations)) {
                continue;
            }

            $context = new Context([
                'namespace' => $method->getNamespaceName(),
                'class' => $declaringClass->getShortName(),
                'method' => $method->name,
                'filename' => $method->getFileName(),
            ]);
            $nestedContext = clone $context;
            $nestedContext->nested = true;
            $implicitAnnotations = [];
            $operations = [];
            $tags = [];
            $security = [];
            foreach (array_merge($annotations, $classAnnotations[$declaringClass->getName()]) as $annotation) {
                $annotation->_context = $context;
                $this->updateNestedAnnotations($annotation, $nestedContext);

                if ($annotation instanceof Operation) {
                    foreach ($httpMethods as $httpMethod) {
                        $annotationClass = $operationAnnotations[$httpMethod];
                        $operation = new $annotationClass(['_context' => $context]);
                        $operation->path = $path;
                        $operation->mergeProperties($annotation);

                        $operations[$httpMethod] = $operation;
                        $analysis->addAnnotation($operation, null);
                    }

                    continue;
                }

                if ($annotation instanceof SWG\Operation) {
                    if (null === $annotation->path) {
                        $annotation = clone $annotation;
                        $annotation->path = $path;
                    }

                    $operations[$annotation->method] = $annotation;
                    $analysis->addAnnotation($annotation, null);

                    continue;
                }

                if ($annotation instanceof Security) {
                    $annotation->validate();
                    $security[] = [$annotation->name => []];

                    continue;
                }

                if ($annotation instanceof SWG\Tag) {
                    $annotation->validate();
                    $tags[] = $annotation->name;

                    continue;
                }

                if (!$annotation instanceof SWG\Response && !$annotation instanceof SWG\Parameter && !$annotation instanceof SWG\ExternalDocumentation) {
                    throw new \LogicException(sprintf('Using the annotation "%s" as a root annotation in "%s::%s()" is not allowed. It should probably be nested in a `@SWG\Response` or `@SWG\Parameter` annotation.', get_class($annotation), $method->getDeclaringClass()->name, $method->name));
                }

                $implicitAnnotations[] = $annotation;
            }

            if (0 === count($implicitAnnotations) && 0 === count($tags) && 0 === count($security)) {
                continue;
            }

            // Registers new annotations
            $analysis->addAnnotations($implicitAnnotations, null);

            foreach ($httpMethods as $httpMethod) {
                $annotationClass = $operationAnnotations[$httpMethod];
                $constructorArg = [
                    '_context' => $context,
                    'path' => $path,
                    'value' => $implicitAnnotations,
                ];

                if (0 !== count($tags)) {
                    $constructorArg['tags'] = $tags;
                }
                if (0 !== count($security)) {
                    $constructorArg['security'] = $security;
                }

                $operation = new $annotationClass($constructorArg);
                if (isset($operations[$httpMethod])) {
                    $operations[$httpMethod]->mergeProperties($operation);
                } else {
                    $analysis->addAnnotation($operation, null);
                }
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
            if ($method = $this->controllerReflector->getReflectionMethod($controller)) {
                $path = $this->normalizePath($route->getPath());
                $httpMethods = $route->getMethods() ?: Swagger::$METHODS;
                $httpMethods = array_map('strtolower', $httpMethods);
                $supportedHttpMethods = array_intersect($httpMethods, Swagger::$METHODS);

                if (empty($supportedHttpMethods)) {
                    $this->logger->warning('None of the HTTP methods specified for path {path} are supported by swagger-ui, skipping this path', [
                        'path' => $path,
                        'methods' => $httpMethods,
                    ]);

                    continue;
                }

                yield $method => [$path, $supportedHttpMethods];
            }
        }
    }

    private function normalizePath(string $path): string
    {
        if ('.{_format}' === substr($path, -10)) {
            $path = substr($path, 0, -10);
        }

        return $path;
    }

    private function updateNestedAnnotations($value, Context $context)
    {
        if ($value instanceof AbstractAnnotation) {
            $value->_context = $context;
        } elseif (!is_array($value)) {
            return;
        }

        foreach ($value as $v) {
            $this->updateNestedAnnotations($v, $context);
        }
    }
}
