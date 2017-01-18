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
use Nelmio\ApiDocBundle\DependencyInjection\EXSystApiDocExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EXSystApiDocBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddDescribersPass());
        $container->addCompilerPass(new AddRouteDescribersPass());
        $container->addCompilerPass(new AddModelDescribersPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new EXSystApiDocExtension();
        }
        if ($this->extension) {
            return $this->extension;
        }
    }
}
