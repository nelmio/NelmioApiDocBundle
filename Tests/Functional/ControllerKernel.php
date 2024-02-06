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
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class ControllerKernel extends Kernel
{
    use MicroKernelTrait;

    private $routeConfiguration;

    /**
     * @var string[]
     */
    private $extraConfigs = [];

    /**
     * @param string[] $extraConfigs
     */
    public function __construct(?callable $routeConfiguration = null, array $extraConfigs = [])
    {
        parent::__construct('test_controller', true);

        $this->routeConfiguration = $routeConfiguration;
        $this->extraConfigs = $extraConfigs;
    }

    public function registerBundles(): iterable
    {
        $bundles = [
            new FrameworkBundle(),
            new NelmioApiDocBundle(),
        ];

        return $bundles;
    }

    protected function configureRoutes(RoutingConfigurator $routes)
    {
        if (null !== $this->routeConfiguration) {
            ($this->routeConfiguration)($routes);
        }
    }


    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->loadFromExtension('framework', ['test' => null]);

        foreach ($this->extraConfigs as $extraConfig) {
            $loader->load($extraConfig);
        }
    }

    public function getCacheDir(): string
    {
        return parent::getCacheDir().'/controller';
    }

    public function getLogDir(): string
    {
        return parent::getLogDir().'/controller';
    }
}
