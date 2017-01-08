<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\SwaggerPhp;

use EXSyst\Component\Swagger\Swagger;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use Swagger\Analysis;
use Swagger\Annotations as SWG;
use Swagger\Context;
use Symfony\Component\Routing\RouteCollection;

/**
 * Automatically resolves the {@link SWG\Operation} linked to
 * {@link SWG\Response}, {@link SWG\Parameter} and
 * {@link SWG\ExternalDocumentation} annotations.
 *
 * @internal
 */
final class OperationResolver
{
    private $routeCollection;
    private $controllerReflector;

    private $controllerMap;

    public function __construct(RouteCollection $routeCollection, ControllerReflector $controllerReflector)
    {
        $this->routeCollection = $routeCollection;
        $this->controllerReflector = $controllerReflector;
    }

    public function __invoke(Analysis $analysis)
    {
        $this->resolveOperationsPath($analysis);
        $this->createImplicitOperations($analysis);
    }

    private function resolveOperationsPath(Analysis $analysis)
    {
        $operations = $analysis->getAnnotationsOfType(SWG\Operation::class);
        foreach ($operations as $operation) {
            if (null !== $operation->path || $operation->_context->not('method')) {
                continue;
            }

            $paths = $this->getPaths($operation->_context, $operation->method);
            if (0 === count($paths)) {
                continue;
            }

            // Define the path of the first annotation
            $operation->path = array_pop($paths);

            // If there are other paths, clone the annotation
            foreach ($paths as $path) {
                $alias = clone $operation;
                $alias->path = $path;

                $analysis->addAnnotation($alias, $alias->_context);
            }
        }
    }

    private function createImplicitOperations(Analysis $analysis)
    {
        $annotations = array_merge($analysis->getAnnotationsOfType(SWG\Response::class), $analysis->getAnnotationsOfType(SWG\Parameter::class), $analysis->getAnnotationsOfType(SWG\ExternalDocumentation::class));
        $map = [];
        foreach ($annotations as $annotation) {
            $context = $annotation->_context;
            if ($context->not('method')) {
                continue;
            }

            $class = $this->getClass($context);
            $method = $context->method;

            $id = $class.'|'.$method;
            if (!isset($map[$id])) {
                $map[$id] = [];
            }

            $map[$id][] = $annotation;
        }

        $operationAnnotations = [
            'get' => SWG\Get::class,
            'post' => SWG\Post::class,
            'put' => SWG\Put::class,
            'patch' => SWG\Patch::class,
            'delete' => SWG\Delete::class,
            'options' => SWG\Options::class,
            'head' => SWG\Head::class,
        ];
        foreach ($map as $id => $annotations) {
            $context = $annotations[0]->_context;
            $httpMethods = $this->getHttpMethods($context);
            foreach ($httpMethods as $httpMethod => $paths) {
                $annotationClass = $operationAnnotations[$httpMethod];
                foreach ($paths as $path => $v) {
                    $operation = new $annotationClass(['path' => $path, 'value' => $annotations], $context);
                    $analysis->addAnnotation($operation, $context);
                }
            }

            foreach ($annotations as $annotation) {
                $analysis->annotations->detach($annotation);
            }
        }
    }

    private function getPaths(Context $context, string $httpMethod): array
    {
        $httpMethods = $this->getHttpMethods($context);
        if (!isset($httpMethods[$httpMethod])) {
            return [];
        }

        return array_keys($httpMethods[$httpMethod]);
    }

    private function getHttpMethods(Context $context)
    {
        if (null === $this->controllerMap) {
            $this->buildMap();
        }

        $class = $this->getClass($context);
        $method = $context->method;

        // Checks if a route corresponds to this method
        if (!isset($this->controllerMap[$class][$method])) {
            return [];
        }

        return $this->controllerMap[$class][$method];
    }

    private function getClass(Context $context)
    {
        return ltrim($context->namespace.'\\'.$context->class, '\\');
    }

    private function buildMap()
    {
        $this->controllerMap = [];
        foreach ($this->routeCollection->all() as $route) {
            if (!$route->hasDefault('_controller')) {
                continue;
            }

            $controller = $route->getDefault('_controller');
            if ($callable = $this->controllerReflector->getReflectionClassAndMethod($controller)) {
                list($class, $method) = $callable;
                $class = $class->name;
                $method = $method->name;

                if (!isset($this->controllerMap[$class])) {
                    $this->controllerMap[$class] = [];
                }
                if (!isset($this->controllerMap[$class][$method])) {
                    $this->controllerMap[$class][$method] = [];
                }

                $httpMethods = $route->getMethods() ?: Swagger::$METHODS;
                foreach ($httpMethods as $httpMethod) {
                    $httpMethod = strtolower($httpMethod);
                    if (!isset($this->controllerMap[$class][$method][$httpMethod])) {
                        $this->controllerMap[$class][$method][$httpMethod] = [];
                    }

                    $path = $this->normalizePath($route->getPath());
                    $this->controllerMap[$class][$method][$httpMethod][$path] = true;
                }
            }
        }
    }

    private function normalizePath(string $path)
    {
        if (substr($path, -10) === '.{_format}') {
            $path = substr($path, 0, -10);
        }

        return $path;
    }
}
