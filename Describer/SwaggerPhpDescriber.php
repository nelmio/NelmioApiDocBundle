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
use Nelmio\ApiDocBundle\SwaggerPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Analysis;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\SwaggerPhp\AddDefaults;
use Nelmio\ApiDocBundle\SwaggerPhp\ModelRegister;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

// Help opcache.preload discover Swagger\Annotations\Swagger
class_exists(OA\OpenApi::class);

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

    public function describe(OA\OpenApi $api)
    {
        $analysis = $this->getAnnotations($api);

        $analysis->process($this->getProcessors());
        $analysis->validate();

        $api->merge(json_decode(json_encode($analysis->openapi), true), $this->overwrite);
    }

    private function getProcessors(): array
    {
        $processors = [
            new AddDefaults(),
            new ModelRegister($this->modelRegistry),
        ];

        return array_merge($processors, Analysis::processors());
    }

    private function getAnnotations(OA\OpenApi $api): Analysis
    {
        $analysis = new Analysis();
        $analysis->addAnnotation(new class($api) extends OA\OpenApi {
            private $api;

            public function __construct(OA\OpenApi $api)
            {
                $this->api = $api;
                parent::__construct([]);
            }

            /**
             * Support definitions from the config and reference to models.
             */
            public function ref($ref)
            {
                /** @var OA\Components $components */#/definitions/
                $components = Util::getChild($this->api, OA\Components::class);

                if (0 === strpos($ref, '#/components/schemas/')
                    && $components->schemas !== OA\UNDEFINED
                    && isset($components->schemas[substr($ref, 21)])
                ) {
                    return;
                }
                if (0 === strpos($ref, '#/components/parameters/')
                    && $components->parameters !== OA\UNDEFINED
                    && isset($components->parameters[substr($ref, 24)])
                ) {
                    return;
                }
                if (0 === strpos($ref, '#/components/responses/')
                    && $components->responses !== OA\UNDEFINED
                    && isset($components->parameters[substr($ref, 23)])
                ) {
                    return;
                }

                parent::ref($ref);
            }
        }, null);

        $operationAnnotations = [
            'get' => OA\Get::class,
            'post' => OA\Post::class,
            'put' => OA\Put::class,
            'patch' => OA\Patch::class,
            'delete' => OA\Delete::class,
            'options' => OA\Options::class,
            'head' => OA\Head::class,
        ];

        $classAnnotations = [];

        /** @var \ReflectionMethod $method */
        foreach ($this->getMethodsToParse() as $method => list($path, $httpMethods)) {
            $declaringClass = $method->getDeclaringClass();
            if (!array_key_exists($declaringClass->getName(), $classAnnotations)) {
                $classAnnotations = array_filter($this->annotationReader->getClassAnnotations($declaringClass), function ($v) {
                    return $v instanceof OA\AbstractAnnotation;
                });
                $classAnnotations[$declaringClass->getName()] = $classAnnotations;
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

                if (
                    !$annotation instanceof OA\Response &&
                    !$annotation instanceof OA\RequestBody &&
                    !$annotation instanceof OA\Parameter &&
                    !$annotation instanceof OA\ExternalDocumentation
                ) {
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
