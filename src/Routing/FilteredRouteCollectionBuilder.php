<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Routing;

use Nelmio\ApiDocBundle\Attribute\Areas;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use OpenApi\Annotations\AbstractAnnotation;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class FilteredRouteCollectionBuilder
{
    private ControllerReflector $controllerReflector;

    private string $area;

    /**
     * @var array<string, mixed>
     */
    private array $options;

    /**
     * @param array<mixed> $options
     */
    public function __construct(
        ControllerReflector $controllerReflector,
        string $area,
        array $options = [],
    ) {
        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'path_patterns' => [],
                'host_patterns' => [],
                'name_patterns' => [],
                'with_attribute' => false,
                'disable_default_routes' => false,
            ])
            ->setAllowedTypes('path_patterns', 'string[]')
            ->setAllowedTypes('host_patterns', 'string[]')
            ->setAllowedTypes('name_patterns', 'string[]')
            ->setAllowedTypes('with_attribute', 'boolean')
            ->setAllowedTypes('disable_default_routes', 'boolean')
        ;

        $this->controllerReflector = $controllerReflector;
        $this->area = $area;
        $this->options = $resolver->resolve($options);
    }

    public function filter(RouteCollection $routes): RouteCollection
    {
        $filteredRoutes = new RouteCollection();
        foreach ($routes->all() as $name => $route) {
            if ($this->matchPath($route)
                && $this->matchHost($route)
                && $this->matchAnnotation($route)
                && $this->matchName($name)
                && $this->defaultRouteDisabled($route)
            ) {
                $filteredRoutes->add($name, $route);
            }
        }

        return $filteredRoutes;
    }

    private function matchPath(Route $route): bool
    {
        foreach ($this->options['path_patterns'] as $pathPattern) {
            if (preg_match('{'.$pathPattern.'}', $route->getPath())) {
                return true;
            }
        }

        return 0 === \count($this->options['path_patterns']);
    }

    private function matchHost(Route $route): bool
    {
        foreach ($this->options['host_patterns'] as $hostPattern) {
            if (preg_match('{'.$hostPattern.'}', $route->getHost())) {
                return true;
            }
        }

        return 0 === \count($this->options['host_patterns']);
    }

    private function matchName(string $name): bool
    {
        foreach ($this->options['name_patterns'] as $namePattern) {
            if (preg_match('{'.$namePattern.'}', $name)) {
                return true;
            }
        }

        return 0 === \count($this->options['name_patterns']);
    }

    private function matchAnnotation(Route $route): bool
    {
        if (false === $this->options['with_attribute']) {
            return true;
        }

        $reflectionMethod = $this->controllerReflector->getReflectionMethod($route->getDefault('_controller'));

        if (null === $reflectionMethod) {
            return false;
        }

        $areas = $this->getAttributesAsAnnotation($reflectionMethod, Areas::class)[0]
            ?? $this->getAttributesAsAnnotation($reflectionMethod->getDeclaringClass(), Areas::class)[0]
            ?? null;

        return null !== $areas && $areas->has($this->area);
    }

    private function defaultRouteDisabled(Route $route): bool
    {
        if (false === $this->options['disable_default_routes']) {
            return true;
        }

        $method = $this->controllerReflector->getReflectionMethod(
            $route->getDefault('_controller') ?? ''
        );

        if (null === $method) {
            return false;
        }

        $annotations = array_map(function (\ReflectionAttribute $attribute) {
            return $attribute->newInstance();
        }, $method->getAttributes(AbstractAnnotation::class, \ReflectionAttribute::IS_INSTANCEOF));

        foreach ($annotations as $annotation) {
            if (str_contains($annotation::class, 'Nelmio\\ApiDocBundle\\Attribute')
                || str_contains($annotation::class, 'OpenApi\\Annotations')
                || str_contains($annotation::class, 'OpenApi\\Attributes')
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \ReflectionClass|\ReflectionMethod $reflection
     *
     * @return Areas[]
     */
    private function getAttributesAsAnnotation($reflection, string $className): array
    {
        $annotations = [];
        foreach ($reflection->getAttributes($className, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $annotations[] = $attribute->newInstance();
        }

        return $annotations;
    }
}
