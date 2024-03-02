<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class ConfigurableContainerFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param Bundle[] $extraBundles
     * @param string[] $extraConfigs
     */
    public function create(array $extraBundles, ?callable $routeConfiguration, array $extraConfigs): void
    {
        // clear cache directory for a fresh container
        $filesystem = new Filesystem();
        $filesystem->remove('var/cache/test');

        $appKernel = new NelmioKernel($extraBundles, $routeConfiguration, $extraConfigs);
        $appKernel->boot();

        $this->container = $appKernel->getContainer();
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
