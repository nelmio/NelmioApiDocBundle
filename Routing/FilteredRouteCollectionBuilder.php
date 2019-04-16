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

use Doctrine\Common\Annotations\Reader;
use Nelmio\ApiDocBundle\Annotation\Areas;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class FilteredRouteCollectionBuilder
{
    /** @var Reader */
    private $annotationReader;

    /** @var ControllerReflector */
    private $controllerReflector;

    /** @var string */
    private $area;

    /** @var array */
    private $options;

    public function __construct(
        Reader $annotationReader,
        ControllerReflector $controllerReflector,
        string $area,
        array $options = []
    ) {
        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'path_patterns' => [],
                'host_patterns' => [],
                'name_patterns' => [],
                'with_annotation' => false,
            ])
            ->setAllowedTypes('path_patterns', 'string[]')
            ->setAllowedTypes('host_patterns', 'string[]')
            ->setAllowedTypes('name_patterns', 'string[]')
            ->setAllowedTypes('with_annotation', 'boolean')
        ;

        if (array_key_exists(0, $options)) {
            @trigger_error(sprintf('Passing an indexed array with a collection of path patterns as argument 1 for `%s()` is deprecated since 3.2.0, expected structure is an array containing parameterized options.', __METHOD__), E_USER_DEPRECATED);

            $normalizedOptions = ['path_patterns' => $options];
            $options = $normalizedOptions;
        }

        $this->annotationReader = $annotationReader;
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

        return 0 === count($this->options['path_patterns']);
    }

    private function matchHost(Route $route): bool
    {
        foreach ($this->options['host_patterns'] as $hostPattern) {
            if (preg_match('{'.$hostPattern.'}', $route->getHost())) {
                return true;
            }
        }

        return 0 === count($this->options['host_patterns']);
    }

    private function matchName(string $name): bool
    {
        foreach ($this->options['name_patterns'] as $namePattern) {
            if (preg_match('{'.$namePattern.'}', $name)) {
                return true;
            }
        }

        return  0 === count($this->options['name_patterns']);
    }

    private function matchAnnotation(Route $route): bool
    {
        if (false === $this->options['with_annotation']) {
            return true;
        }

        $method = $this->controllerReflector->getReflectionMethod(
            $route->getDefault('_controller') ?? ''
        );
        if (null === $method) {
            return false;
        }

        /** @var null|Areas $areas */
        $areas = $this->annotationReader->getMethodAnnotation(
            $method,
            Areas::class
        );

        return (null !== $areas) ? $areas->has($this->area) : false;
    }
}
