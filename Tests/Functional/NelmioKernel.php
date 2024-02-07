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
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class NelmioKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @var Bundle[]
     */
    private $extraBundles;

    private $routeConfiguration;

    /**
     * @var string[]
     */
    private $extraConfigs;

    /**
     * @param Bundle[] $extraBundles
     * @param string[] $extraConfigs
     */
    public function __construct(array $extraBundles, ?callable $routeConfiguration, array $extraConfigs)
    {
        parent::__construct('test', true);

        $this->extraBundles = $extraBundles;
        $this->routeConfiguration = $routeConfiguration;
        $this->extraConfigs = $extraConfigs;
    }

    public function registerBundles(): iterable
    {
        $bundles = [
            new FrameworkBundle(),
            new NelmioApiDocBundle(),
        ];

        return array_merge($bundles, $this->extraBundles);
    }

    protected function configureRoutes(RoutingConfigurator $routes)
    {
        if (null !== $this->routeConfiguration) {
            ($this->routeConfiguration)($routes);
        }
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $framework = [
            'assets' => true,
            'secret' => 'MySecretKey',
            'test' => null,
            'validation' => null,
            'serializer' => [],
        ];

        if (TestKernel::isAnnotationsAvailable()) {
            $loader->load(__DIR__.'/Configs/annotations.yaml');
        }

        $container->loadFromExtension('framework', $framework);

        foreach ($this->extraConfigs as $extraConfig) {
            $loader->load($extraConfig);
        }
    }
}
