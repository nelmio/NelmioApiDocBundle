<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Util;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 */
class ControllerReflector
{
    private $container;

    private $controllerNameParser;

    private $controllers = [];

    public function __construct(ContainerInterface $container, ControllerNameParser $controllerNameParser)
    {
        $this->container = $container;
        $this->controllerNameParser = $controllerNameParser;
    }

    /**
     * Returns the ReflectionMethod for the given controller string.
     *
     * @param string $controller
     *
     * @return \ReflectionMethod|null
     */
    public function getReflectionMethod(string $controller)
    {
        $callable = $this->getClassAndMethod($controller);
        if (null === $callable) {
            return;
        }

        list($class, $method) = $callable;

        try {
            return new \ReflectionMethod($class, $method);
        } catch (\ReflectionException $e) {
            // In case we can't reflect the controller, we just
            // ignore the route
        }
    }

    public function getReflectionClassAndMethod(string $controller)
    {
        $callable = $this->getClassAndMethod($controller);
        if (null === $callable) {
            return;
        }

        list($class, $method) = $callable;

        try {
            return [new \ReflectionClass($class), new \ReflectionMethod($class, $method)];
        } catch (\ReflectionException $e) {
            // In case we can't reflect the controller, we just
            // ignore the route
        }
    }

    private function getClassAndMethod(string $controller)
    {
        if (isset($this->controllers[$controller])) {
            return $this->controllers[$controller];
        }

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
                $class = get_class($this->container->get($controller));
                if (class_exists(ClassUtils::class)) {
                    $class = ClassUtils::getRealClass($class);
                }

                if (!isset($method) && method_exists($class, '__invoke')) {
                    $method = '__invoke';
                }
            }
        }

        if (!isset($class) || !isset($method)) {
            $this->controllers[$controller] = null;

            return;
        }

        return $this->controllers[$controller] = [$class, $method];
    }
}
