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

use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberInterface;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\RouteCollection;

final class RouteDescriber implements DescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    /**
     * @param RouteDescriberInterface[]|iterable $routeDescribers
     */
    public function __construct(
        private RouteCollection $routeCollection,
        private ControllerReflector $controllerReflector,
        private iterable $routeDescribers
    ) {
    }

    public function describe(OA\OpenApi $api)
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
                // Extract as many information as possible about this route
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
