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
use Nelmio\ApiDocBundle\SwaggerPhp\AddDefaults;
use Nelmio\ApiDocBundle\SwaggerPhp\ModelRegister;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use Swagger\Analysis;
use Swagger\Annotations\AbstractAnnotation;
use Swagger\Annotations as SWG;
use Swagger\Context;
use Symfony\Component\Routing\RouteCollection;

final class SwaggerPhpDescriber extends ExternalDocDescriber implements ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    private $routeCollection;
    private $controllerReflector;
    private $annotationReader;

    public function __construct(RouteCollection $routeCollection, ControllerReflector $controllerReflector, Reader $annotationReader, bool $overwrite = false)
    {
        $this->routeCollection = $routeCollection;
        $this->controllerReflector = $controllerReflector;
        $this->annotationReader = $annotationReader;

        parent::__construct(function () {
            $analysis = $this->getAnnotations();

            $analysis->process($this->getProcessors());
            $analysis->validate();

            return json_decode(json_encode($analysis->swagger));
        }, $overwrite);
    }

    private function getProcessors(): array
    {
        $processors = [
            new AddDefaults(),
            new ModelRegister($this->modelRegistry),
        ];

        return array_merge($processors, Analysis::processors());
    }

    private function getAnnotations(): Analysis
    {
        $analysis = new Analysis();

        $operationAnnotations = [
            'get' => SWG\Get::class,
            'post' => SWG\Post::class,
            'put' => SWG\Put::class,
            'patch' => SWG\Patch::class,
            'delete' => SWG\Delete::class,
            'options' => SWG\Options::class,
            'head' => SWG\Head::class,
        ];

        foreach ($this->getMethodsToParse() as $method => list($path, $httpMethods)) {
            $annotations = array_filter($this->annotationReader->getMethodAnnotations($method), function ($v) {
                return $v instanceof SWG\AbstractAnnotation;
            });

            if (0 === count($annotations)) {
                continue;
            }

            $declaringClass = $method->getDeclaringClass();
            $context = new Context([
                'namespace' => $method->getNamespaceName(),
                'class' => $declaringClass->getShortName(),
                'method' => $method->name,
                'filename' => $method->getFileName(),
            ]);
            $nestedContext = clone $context;
            $nestedContext->nested = true;
            $implicitAnnotations = [];
            $tags = [];
            foreach ($annotations as $annotation) {
                $annotation->_context = $context;
                $this->updateNestedAnnotations($annotation, $nestedContext);

                if ($annotation instanceof Operation) {
                    foreach ($httpMethods as $httpMethod) {
                        $annotationClass = $operationAnnotations[$httpMethod];
                        $operation = new $annotationClass(['_context' => $context]);
                        $operation->path = $path;
                        $operation->mergeProperties($annotation);

                        $analysis->addAnnotation($operation, null);
                    }

                    continue;
                }

                if ($annotation instanceof SWG\Operation) {
                    if (null === $annotation->path) {
                        $annotation = clone $annotation;
                        $annotation->path = $path;
                    }

                    $analysis->addAnnotation($annotation, null);

                    continue;
                }

                if ($annotation instanceof SWG\Tag) {
                    $annotation->validate();
                    $tags[] = $annotation->name;

                    continue;
                }

                if (!$annotation instanceof SWG\Response && !$annotation instanceof SWG\Parameter && !$annotation instanceof SWG\ExternalDocumentation) {
                    throw new \LogicException(sprintf('Using the annotation "%s" as a root annotation in "%s::%s()" is not allowed.', get_class($annotation), $method->getDeclaringClass()->name, $method->name));
                }

                $implicitAnnotations[] = $annotation;
            }

            if (0 === count($implicitAnnotations) && 0 === count($tags)) {
                continue;
            }

            foreach ($httpMethods as $httpMethod) {
                $annotationClass = $operationAnnotations[$httpMethod];
                $operation = new $annotationClass(['_context' => $context, 'path' => $path, 'value' => $implicitAnnotations, 'tags' => $tags]);
                $analysis->addAnnotation($operation, null);
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
            if ($callable = $this->controllerReflector->getReflectionClassAndMethod($controller)) {
                list($class, $method) = $callable;
                $path = $this->normalizePath($route->getPath());
                $httpMethods = $route->getMethods() ?: Swagger::$METHODS;
                $httpMethods = array_map('strtolower', $httpMethods);

                yield $method => [$path, $httpMethods];
            }
        }
    }

    private function normalizePath(string $path): string
    {
        if (substr($path, -10) === '.{_format}') {
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
