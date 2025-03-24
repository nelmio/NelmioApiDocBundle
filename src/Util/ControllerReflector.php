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

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 */
class ControllerReflector
{
    private ContainerInterface $container;
    /**
     * @var array<string, array{string, string}|null>
     */
    private array $controllers = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns the ReflectionMethod for the given controller string.
     *
     * @param string|array{string, string}|null $controller
     */
    public function getReflectionMethod($controller): ?\ReflectionMethod
    {
        if (\is_string($controller)) {
            $controller = $this->getClassAndMethod($controller);
        }

        if (null === $controller) {
            return null;
        }

        return $this->getReflectionMethodByClassNameAndMethodName(...$controller);
    }

    private function getReflectionMethodByClassNameAndMethodName(string $class, string $method): ?\ReflectionMethod
    {
        try {
            return new \ReflectionMethod($class, $method);
        } catch (\ReflectionException $e) {
            // In case we can't reflect the controller, we just ignore the route
        }

        return null;
    }

    /**
     * @return array{string, string}|null
     */
    private function getClassAndMethod(string $controller): ?array
    {
        if (isset($this->controllers[$controller])) {
            return $this->controllers[$controller];
        }

        if (preg_match('#(.+)::([\w]+)#', $controller, $matches)) {
            $class = $matches[1];
            $method = $matches[2];

            if (!class_exists($class) && $this->container->has($class)) {
                $class = \get_class($this->container->get($class));
            }

            return $this->controllers[$controller] = [$class, $method];
        }

        if (class_exists($controller)) {
            return $this->controllers[$controller] = [$controller, '__invoke'];
        }

        $this->controllers[$controller] = null;

        return null;
    }
}
