<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional;

use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class NelmioKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @param Bundle[]                    $extraBundles
     * @param array<string, array<mixed>> $extraConfigs     Key is the extension name, value is the config
     * @param array<string, Definition>   $extraDefinitions
     */
    public function __construct(
        private array $extraBundles,
        private ?\Closure $routeConfiguration,
        private array $extraConfigs,
        private array $extraDefinitions,
    ) {
        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        $bundles = [
            new FrameworkBundle(),
            new NelmioApiDocBundle(),
        ];

        return array_merge($bundles, $this->extraBundles);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        if (null !== $this->routeConfiguration) {
            ($this->routeConfiguration)($routes);
        }
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->loadFromExtension('framework', ['test' => null]);

        foreach ($this->extraConfigs as $extensionName => $config) {
            $container->loadFromExtension($extensionName, $config);
        }

        foreach ($this->extraDefinitions as $id => $definition) {
            $container->setDefinition($id, $definition);
        }
    }
}
