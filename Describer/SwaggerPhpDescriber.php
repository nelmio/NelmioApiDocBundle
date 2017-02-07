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

use Nelmio\ApiDocBundle\SwaggerPhp\AddDefaults;
use Nelmio\ApiDocBundle\SwaggerPhp\ModelRegister;
use Nelmio\ApiDocBundle\SwaggerPhp\OperationResolver;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use Swagger\Analyser;
use Swagger\Analysis;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\RouteCollection;

final class SwaggerPhpDescriber extends ExternalDocDescriber implements ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    private $routeCollection;
    private $controllerReflector;

    public function __construct(RouteCollection $routeCollection, ControllerReflector $controllerReflector, bool $overwrite = false)
    {
        $this->routeCollection = $routeCollection;
        $this->controllerReflector = $controllerReflector;

        parent::__construct(function () {
            $whitelist = Analyser::$whitelist;
            Analyser::$whitelist = false;
            try {
                $options = ['processors' => $this->getProcessors()];
                $annotation = \Swagger\scan($this->getFinder(), $options);

                return json_decode(json_encode($annotation));
            } finally {
                Analyser::$whitelist = $whitelist;
            }
        }, $overwrite);
    }

    private function getFinder()
    {
        $files = [];
        foreach ($this->routeCollection->all() as $route) {
            if (!$route->hasDefault('_controller')) {
                continue;
            }

            // if able to resolve the controller
            $controller = $route->getDefault('_controller');
            if ($callable = $this->controllerReflector->getReflectionClassAndMethod($controller)) {
                list($class, $method) = $callable;

                $files[$class->getFileName()] = true;
            }
        }

        $finder = new Finder();
        $finder->append(array_keys($files));

        return $finder;
    }

    private function getProcessors(): array
    {
        $processors = [
            new AddDefaults(),
            new ModelRegister($this->modelRegistry),
            new OperationResolver($this->routeCollection, $this->controllerReflector),
        ];

        return array_merge($processors, Analysis::processors());
    }
}
