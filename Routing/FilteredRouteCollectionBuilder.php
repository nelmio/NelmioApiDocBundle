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

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class FilteredRouteCollectionBuilder
{
    /** @var RequestStack */
    private $requestStack;
    /** @var array */
    private $routesConfig;

    public function __construct(RequestStack $requestStack, array $routesConfig = [])
    {
        $this->requestStack = $requestStack;
        $this->routesConfig = $routesConfig;
    }

    public function filter(RouteCollection $routes): RouteCollection
    {
        $filteredRoutes = new RouteCollection();
        foreach ($routes->all() as $name => $route) {
            if ($this->match($route)) {
                $filteredRoutes->add($name, $route);
            }
        }

        return $filteredRoutes;
    }

    private function match(Route $route): bool
    {
        $actualHost = $this->requestStack->getCurrentRequest()->getHost();
        foreach ($this->routesConfig as $oneRouteConfig) {
            if (array_key_exists('host', $oneRouteConfig) && $oneRouteConfig['host'] !== null && $oneRouteConfig['host'] !== $actualHost) {
                continue;
            }
            foreach ($oneRouteConfig['path_patterns'] as $pathPattern) {
                if (preg_match('{' . $pathPattern . '}', $route->getPath())) {
                    return true;
                }
            }
        }

        return false;
    }
}
