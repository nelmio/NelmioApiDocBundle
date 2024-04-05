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

use Nelmio\ApiDocBundle\DependencyInjection\Compiler\ConfigurationPass;
use Nelmio\ApiDocBundle\DependencyInjection\Compiler\CustomProcessorPass;
use Nelmio\ApiDocBundle\DependencyInjection\Compiler\PhpDocExtractorPass;
use Nelmio\ApiDocBundle\DependencyInjection\Compiler\TagDescribersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NelmioApiDocBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ConfigurationPass());
        $container->addCompilerPass(new TagDescribersPass());
        $container->addCompilerPass(new PhpDocExtractorPass());
        $container->addCompilerPass(new CustomProcessorPass());
    }

    /**
     * Allows using the new directory structure on Symfony < 6.1.
     * Without this no proper namespace is set for twig templates.
     *
     * @see \Symfony\Component\HttpKernel\Bundle\AbstractBundle::getPath()
     */
    public function getPath(): string
    {
        if (!isset($this->path)) {
            $reflected = new \ReflectionObject($this);
            // assume the modern directory structure by default
            $this->path = \dirname($reflected->getFileName(), 2);
        }

        return $this->path;
    }
}
