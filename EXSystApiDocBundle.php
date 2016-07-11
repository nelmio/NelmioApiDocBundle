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

use EXSyst\Bundle\ApiDocBundle\DependencyInjection\Compiler\AddExtractorsPass;
use EXSyst\Bundle\ApiDocBundle\DependencyInjection\Compiler\AddRoutingExtractorsPass;
use EXSyst\Bundle\ApiDocBundle\DependencyInjection\EXSystApiDocExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EXSystApiDocBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddExtractorsPass());
        $container->addCompilerPass(new AddRoutingExtractorsPass());
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
