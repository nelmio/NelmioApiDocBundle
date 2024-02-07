<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional;

use Symfony\Component\DependencyInjection\ContainerInterface;
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
        $appKernel = new NelmioKernel($extraBundles, $routeConfiguration, $extraConfigs);
        $appKernel->boot();

        $this->container = $appKernel->getContainer();
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
