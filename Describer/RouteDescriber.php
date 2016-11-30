<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\Describer;

use Doctrine\Common\Util\ClassUtils;
use EXSyst\Bundle\ApiDocBundle\RouteDescriber\RouteDescriberInterface;
use EXSyst\Component\Swagger\Swagger;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class RouteDescriber implements DescriberInterface
{
    private $container;
    private $routeCollection;
    private $controllerNameParser;
    private $routeDescribers;

    /**
     * @param ContainerInterface        $container
     * @param RouteCollection           $routeCollection
     * @param ControllerNameParser      $controllerNameParser
     * @param RouteDescriberInterface[] $routeDescribers
     */
    public function __construct(ContainerInterface $container, RouteCollection $routeCollection, ControllerNameParser $controllerNameParser, array $routeDescribers)
    {
        $this->container = $container;
        $this->routeCollection = $routeCollection;
        $this->controllerNameParser = $controllerNameParser;
        $this->routeDescribers = $routeDescribers;
    }

    public function describe(Swagger $api)
    {
        if (0 === count($this->routeDescribers)) {
            return;
        }

        foreach ($this->routeCollection->all() as $route) {
            // if able to resolve the controller
            if ($method = $this->getReflectionMethod($route->getDefault('_controller'))) {
                // Extract as many informations as possible about this route
                foreach ($this->routeDescribers as $describer) {
                    $describer->describe($api, $route, $method);
                }
            }
        }
    }

    /**
     * Returns the ReflectionMethod for the given controller string.
     *
     * @param string $controller
     *
     * @return \ReflectionMethod|null
     */
    private function getReflectionMethod(string $controller)
    {
        if (false === strpos($controller, '::') && 2 === substr_count($controller, ':')) {
            $controller = $this->controllerNameParser->parse($controller);
        }

        if (preg_match('#(.+)::([\w]+)#', $controller, $matches)) {
            $class = $matches[1];
            $method = $matches[2];
        } elseif (class_exists($controller)) {
            $class = $controller;
            $method = '__invoke';
        } else {
            if (preg_match('#(.+):([\w]+)#', $controller, $matches)) {
                $controller = $matches[1];
                $method = $matches[2];
            }

            if ($this->container->has($controller)) {
                if (class_exists(ClassUtils::class)) {
                    $class = ClassUtils::getRealClass(get_class($this->container->get($controller)));
                }

                if (!isset($method) && method_exists($class, '__invoke')) {
                    $method = '__invoke';
                }
            }
        }

        if (isset($class) && isset($method)) {
            try {
                return new \ReflectionMethod($class, $method);
            } catch (\ReflectionException $e) {
                // In case we can't reflect the controller, we just
                // ignore the route
            }
        }
    }
}
