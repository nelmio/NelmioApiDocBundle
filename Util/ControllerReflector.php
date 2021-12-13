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
use Symfony\Component\HttpKernel\Kernel;

/**
 * @internal
 */
class ControllerReflector
{
    private mixed $controllerNameParser;
    private array $controllers = [];

    public function __construct(private ContainerInterface $container)
    {
        if (1 < \func_num_args() && func_get_arg(1) instanceof ControllerNameParser) {
            $this->controllerNameParser = func_get_arg(1);
        }
    }

    /**
     * Returns the ReflectionMethod for the given controller string.
     */
    public function getReflectionMethod($controller): ?\ReflectionMethod
    {
        if (is_string($controller)) {
            $controller = $this->getClassAndMethod($controller);
        }

        if (null === $controller) {
            return null;
        }

        return $this->geReflectionMethodByClassNameAndMethodName(...$controller);
    }

    public function geReflectionMethodByClassNameAndMethodName(string $class, string $method): ?\ReflectionMethod
    {
        try {
            return new \ReflectionMethod($class, $method);
        } catch (\ReflectionException) {
            // In case we can't reflect the controller, we just
            // ignore the route
        }

        return null;
    }

    private function getClassAndMethod(string $controller)
    {
        if (isset($this->controllers[$controller])) {
            return $this->controllers[$controller];
        }

        if ($this->controllerNameParser && !str_contains($controller, '::') && 2 === substr_count($controller, ':')) {
            $deprecatedNotation = $controller;

            try {
                $controller = $this->controllerNameParser->parse($controller);

                @trigger_error(sprintf('Referencing controllers with %s is deprecated since Symfony 4.1, use "%s" instead.', $deprecatedNotation, $controller), E_USER_DEPRECATED);
            } catch (\InvalidArgumentException) {
                // unable to optimize unknown notation
            }
        }

        if (preg_match('#(.+)::([\w]+)#', $controller, $matches)) {
            $class = $matches[1];
            $method = $matches[2];
            // Since symfony 4.1 routes are defined like service_id::method_name
            if (Kernel::VERSION_ID >= 40100 && !class_exists($class)) {
                if ($this->container->has($class)) {
                    $class = $this->container->get($class)::class;
                    if (class_exists(ClassUtils::class)) {
                        $class = ClassUtils::getRealClass($class);
                    }
                }
            }
        } elseif (class_exists($controller)) {
            $class = $controller;
            $method = '__invoke';
        } else {
            // Has to be removed when dropping support of symfony < 4.1
            if (preg_match('#(.+):([\w]+)#', $controller, $matches)) {
                $controller = $matches[1];
                $method = $matches[2];
            }

            if ($this->container->has($controller)) {
                $class = $this->container->get($controller)::class;
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

            return null;
        }

        return $this->controllers[$controller] = [$class, $method];
    }
}
