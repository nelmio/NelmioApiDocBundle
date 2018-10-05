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

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class FilteredRouteCollectionBuilder
{
    private $options;

    public function __construct(array $options = [])
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'path_patterns' => [],
                'host_patterns' => [],
            ])
            ->setAllowedTypes('path_patterns', 'string[]')
            ->setAllowedTypes('host_patterns', 'string[]')
        ;

        if (array_key_exists(0, $options)) {
            @trigger_error(sprintf('Passing an indexed array with a collection of path patterns as argument 1 for `%s()` is deprecated since 3.2.0, expected structure is an array containing parameterized options.', __METHOD__), E_USER_DEPRECATED);

            $normalizedOptions = ['path_patterns' => $options];
            $options = $normalizedOptions;
        }

        $this->options = $resolver->resolve($options);
    }

    public function filter(RouteCollection $routes): RouteCollection
    {
        $filteredRoutes = new RouteCollection();
        foreach ($routes->all() as $name => $route) {
            if ($this->matchPath($route) && $this->matchHost($route)) {
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
}
