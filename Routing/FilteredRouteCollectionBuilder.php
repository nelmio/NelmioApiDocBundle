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

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class FilteredRouteCollectionBuilder
{
    private $pathPatterns;
    private $checkDefault;
    private $area;

    public function __construct(array $pathPatterns = [], $checkDefault = null, $area = null)
    {
        $this->pathPatterns = $pathPatterns;
        $this->checkDefault = $checkDefault;
        $this->area = $area;
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
        if (null !== $this->area && null !== $this->checkDefault) {
            $areas = $route->getDefault($this->checkDefault);
            if (!is_array($areas) && !empty($areas)) {
                $areas = [$areas];
            }

            if (empty($areas) || !in_array($this->area, $areas)) {
                return false;
            }
        }

        foreach ($this->pathPatterns as $pathPattern) {
            if (preg_match('{'.$pathPattern.'}', $route->getPath())) {
                return true;
            }
        }

        return false;
    }
}
