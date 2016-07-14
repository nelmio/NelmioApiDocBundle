<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle;

use EXSyst\Bundle\ApiDocBundle\DependencyInjection\Compiler\AddDescribersPass;
use EXSyst\Bundle\ApiDocBundle\DependencyInjection\Compiler\AddRouteDescribersPass;
use EXSyst\Bundle\ApiDocBundle\DependencyInjection\EXSystApiDocExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EXSystApiDocBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddDescribersPass());
        $container->addCompilerPass(new AddRouteDescribersPass());
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
