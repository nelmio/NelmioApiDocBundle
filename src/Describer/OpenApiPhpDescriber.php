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
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberTrait;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use Nelmio\ApiDocBundle\Util\SetsContextTrait;
use OpenApi\Analysers\AttributeAnnotationFactory;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

// Help opcache.preload discover Swagger\Annotations\Swagger
class_exists(OA\OpenApi::class);

final class OpenApiPhpDescriber
{
    use RouteDescriberTrait;
    use SetsContextTrait;

    public function __construct(
        private readonly RouteCollection $routeCollection,
        private readonly ControllerReflector $controllerReflector,
        private readonly OperationIdGeneration $operationIdGeneration = OperationIdGeneration::ALWAYS_PREPEND,
    ) {
    }

    public function describe(OA\OpenApi $api): void
    {
        $classAnnotations = [];

        foreach ($this->getRoutesToParse() as $routeName => $route) {
            $controller = $route->getDefault('_controller');
            $reflectedMethod = $this->controllerReflector->getReflectionMethod($controller);
            if (null === $reflectedMethod) {
                continue;
            }

            $path = $this->normalizePath($route->getPath());
            $supportedHttpMethods = $this->getSupportedHttpMethods($route);

            $classReflector = $reflectedMethod->getDeclaringClass();
            try {
                if (\is_array($controller) && method_exists(...$controller)) {
                    $classReflector = new \ReflectionClass($controller[0]);
                } elseif (\is_string($controller) && false !== $i = strpos($controller, '::')) {
                    $classReflector = new \ReflectionClass(substr($controller, 0, $i));
                }
            } catch (\ReflectionException) {
                // Fallback to the declaring class if the controller class does not exist
            }

            $path = Util::getPath($api, $path);

            $context = Util::createContext(['nested' => $path], $path->_context);
            $context->namespace = $classReflector->getNamespaceName();
            $context->class = $classReflector->getShortName();
            $context->method = $reflectedMethod->name;
            $context->filename = $reflectedMethod->getFileName();

            $this->setContext($context);

            if (!\array_key_exists($classReflector->getName(), $classAnnotations)) {
                $classAnnotations[$classReflector->getName()] ??= $this->getAttributesAsAnnotation($classReflector, $context);
            }

            $annotations = $this->getAttributesAsAnnotation($reflectedMethod, $context);

            $implicitAnnotations = [];
            $mergeProperties = new \stdClass();

            foreach (array_merge($annotations, $classAnnotations[$classReflector->getName()]) as $annotation) {
                if ($annotation instanceof Operation) {
                    foreach ($supportedHttpMethods as $httpMethod) {
                        $operation = Util::getOperation($path, $httpMethod);
                        $operation->mergeProperties($annotation);
                    }

                    continue;
                }

                if ($annotation instanceof OA\Operation) {
                    if (!\in_array($annotation->method, $supportedHttpMethods, true)) {
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

                    foreach ($supportedHttpMethods as $httpMethod) {
                        $operation = Util::getOperation($path, $httpMethod);

                        if (Generator::UNDEFINED === $operation->security) {
                            $operation->security = [];
                        }

                        if (null === $annotation->name) {
                            $operation->security = [];

                            continue;
                        }

                        $operation->security[] = [$annotation->name => $annotation->scopes];
                    }

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
                    throw new \LogicException(\sprintf('Using the annotation "%s" as a root annotation in "%s::%s()" is not allowed.', $annotation::class, $reflectedMethod->getDeclaringClass()->name, $reflectedMethod->name));
                }

                $implicitAnnotations[] = $annotation;
            }

            foreach ($supportedHttpMethods as $httpMethod) {
                $operation = Util::getOperation($path, $httpMethod);
                if ([] !== $implicitAnnotations) {
                    $operation->merge($implicitAnnotations);
                }
                if ([] !== get_object_vars($mergeProperties)) {
                    $operation->mergeProperties($mergeProperties);
                }

                if (Generator::UNDEFINED === $operation->operationId) {
                    $operation->operationId = match ($this->operationIdGeneration) {
                        OperationIdGeneration::ALWAYS_PREPEND => $httpMethod.'_'.$routeName,
                        OperationIdGeneration::CONDITIONALLY_PREPEND => str_starts_with($routeName, $httpMethod) ? $routeName : $httpMethod.'_'.$routeName,
                        OperationIdGeneration::NO_PREPEND => $routeName,
                    };
                }
            }
        }

        // Reset the Generator after the parsing
        $this->setContext(null);
    }

    private function getRoutesToParse(): \Generator
    {
        yield from $this->routeCollection->all();
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
