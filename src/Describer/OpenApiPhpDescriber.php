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

use Nelmio\ApiDocBundle\Attribute\Operation;
use Nelmio\ApiDocBundle\Attribute\Security;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use Nelmio\ApiDocBundle\Util\SetsContextTrait;
use OpenApi\Analysers\AttributeAnnotationFactory;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

// Help opcache.preload discover Swagger\Annotations\Swagger
class_exists(OA\OpenApi::class);

final class OpenApiPhpDescriber
{
    use SetsContextTrait;

    private RouteCollection $routeCollection;
    private ControllerReflector $controllerReflector;
    private LoggerInterface $logger;

    public function __construct(RouteCollection $routeCollection, ControllerReflector $controllerReflector, LoggerInterface $logger)
    {
        $this->routeCollection = $routeCollection;
        $this->controllerReflector = $controllerReflector;
        $this->logger = $logger;
    }

    public function describe(OA\OpenApi $api): void
    {
        $classAnnotations = [];

        /** @var \ReflectionMethod $method */
        foreach ($this->getMethodsToParse() as $method => [$path, $httpMethods, $routeName]) {
            $declaringClass = $method->getDeclaringClass();

            $path = Util::getPath($api, $path);

            $context = Util::createContext(['nested' => $path], $path->_context);
            $context->namespace = $declaringClass->getNamespaceName();
            $context->class = $declaringClass->getShortName();
            $context->method = $method->name;
            $context->filename = $method->getFileName();

            $this->setContext($context);

            if (!\array_key_exists($declaringClass->getName(), $classAnnotations)) {
                $classAnnotations[$declaringClass->getName()] = $this->getAttributesAsAnnotation($declaringClass, $context);
            }

            $annotations = $this->getAttributesAsAnnotation($method, $context);

            $implicitAnnotations = [];
            $mergeProperties = new \stdClass();

            foreach (array_merge($annotations, $classAnnotations[$declaringClass->getName()]) as $annotation) {
                if ($annotation instanceof Operation) {
                    foreach ($httpMethods as $httpMethod) {
                        $operation = Util::getOperation($path, $httpMethod);
                        $operation->mergeProperties($annotation);
                    }

                    continue;
                }

                if ($annotation instanceof OA\Operation) {
                    if (!\in_array($annotation->method, $httpMethods, true)) {
                        continue;
                    }
                    if (Generator::UNDEFINED !== $annotation->path && $path->path !== $annotation->path) {
                        continue;
                    }

                    $operation = Util::getOperation($path, $annotation->method);
                    $operation->mergeProperties($annotation);

                    continue;
                }

                if ($annotation instanceof Security) {
                    $annotation->validate();

                    if (null === $annotation->name) {
                        $mergeProperties->security = [];

                        continue;
                    }

                    $mergeProperties->security[] = [$annotation->name => $annotation->scopes];

                    continue;
                }

                if ($annotation instanceof OA\Tag) {
                    $annotation->validate();
                    $mergeProperties->tags[] = $annotation->name;

                    $tag = Util::getTag($api, $annotation->name);
                    $tag->mergeProperties($annotation);

                    continue;
                }

                if (
                    !$annotation instanceof OA\Response
                    && !$annotation instanceof OA\RequestBody
                    && !$annotation instanceof OA\Parameter
                    && !$annotation instanceof OA\ExternalDocumentation
                ) {
                    throw new \LogicException(\sprintf('Using the annotation "%s" as a root annotation in "%s::%s()" is not allowed.', $annotation::class, $method->getDeclaringClass()->name, $method->name));
                }

                $implicitAnnotations[] = $annotation;
            }

            foreach ($httpMethods as $httpMethod) {
                $operation = Util::getOperation($path, $httpMethod);
                if ([] !== $implicitAnnotations) {
                    $operation->merge($implicitAnnotations);
                }
                if ([] !== get_object_vars($mergeProperties)) {
                    $operation->mergeProperties($mergeProperties);
                }

                if (Generator::UNDEFINED === $operation->operationId) {
                    $operation->operationId = $httpMethod.'_'.$routeName;
                }
            }
        }

        // Reset the Generator after the parsing
        $this->setContext(null);
    }

    private function getMethodsToParse(): \Generator
    {
        foreach ($this->routeCollection->all() as $routeName => $route) {
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
            if ([] === $supportedHttpMethods) {
                $this->logger->warning('None of the HTTP methods specified for path {path} are supported by swagger-ui, skipping this path', [
                    'path' => $path,
                ]);

                continue;
            }
            yield $reflectedMethod => [$path, $supportedHttpMethods, $routeName];
        }
    }

    /**
     * @return string[]
     */
    private function getSupportedHttpMethods(Route $route): array
    {
        $allMethods = Util::OPERATIONS;
        $methods = array_map('strtolower', $route->getMethods());

        // an empty array means that any method is allowed
        if ([] === $methods) {
            return $allMethods;
        }

        return array_intersect($methods, $allMethods);
    }

    private function normalizePath(string $path): string
    {
        if ('.{_format}' === substr($path, -10)) {
            $path = substr($path, 0, -10);
        }

        return $path;
    }

    /**
     * @param \ReflectionClass<object>|\ReflectionMethod $reflection
     *
     * @return OA\AbstractAnnotation[]
     */
    private function getAttributesAsAnnotation($reflection, \OpenApi\Context $context): array
    {
        $attributesFactory = new AttributeAnnotationFactory();
        $attributes = $attributesFactory->build($reflection, $context);
        // The attributes factory removes the context after executing so we need to set it back...
        $this->setContext($context);

        return $attributes;
    }
}
