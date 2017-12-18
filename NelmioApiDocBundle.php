<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle;

use Nelmio\ApiDocBundle\DependencyInjection\Compiler\AddDescribersPass;
use Nelmio\ApiDocBundle\DependencyInjection\Compiler\AddModelDescribersPass;
use Nelmio\ApiDocBundle\DependencyInjection\Compiler\AddRouteDescribersPass;
use Nelmio\ApiDocBundle\DependencyInjection\Compiler\ConfigurationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NelmioApiDocBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ConfigurationPass());
        $container->addCompilerPass(new AddDescribersPass());
        $container->addCompilerPass(new AddModelDescribersPass());
        $container->addCompilerPass(new AddRouteDescribersPass());
    }
}
