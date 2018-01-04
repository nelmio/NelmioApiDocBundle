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

use EXSyst\Component\Swagger\Swagger;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberInterface;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class RouteDescriber implements DescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    private $routeCollection;

    private $controllerReflector;

    private $routeDescribers;

    /**
     * @param RouteCollection                    $routeCollection
     * @param ControllerReflector                $controllerReflector
     * @param RouteDescriberInterface[]|iterable $routeDescribers
     */
    public function __construct(RouteCollection $routeCollection, ControllerReflector $controllerReflector, $routeDescribers)
    {
        $this->routeCollection = $routeCollection;
        $this->controllerReflector = $controllerReflector;
        $this->routeDescribers = $routeDescribers;
    }

    public function describe(Swagger $api)
    {
        if (0 === count($this->routeDescribers)) {
            return;
        }

        foreach ($this->routeCollection->all() as $route) {
            if (!$route->hasDefault('_controller')) {
                continue;
            }

            // if able to resolve the controller
            $controller = $route->getDefault('_controller');
            if ($method = $this->controllerReflector->getReflectionMethod($controller)) {
                // Extract as many informations as possible about this route
                foreach ($this->routeDescribers as $describer) {
                    if ($describer instanceof ModelRegistryAwareInterface) {
                        $describer->setModelRegistry($this->modelRegistry);
                    }

                    $describer->describe($api, $route, $method);
                }
            }
        }
    }
}
