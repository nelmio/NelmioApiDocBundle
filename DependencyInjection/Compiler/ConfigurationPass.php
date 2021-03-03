<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\DependencyInjection\Compiler;

use Nelmio\ApiDocBundle\ModelDescriber\FormModelDescriber;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Enables the FormModelDescriber only if forms are enabled.
 *
 * @internal
 */
final class ConfigurationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('form.factory')) {
            $container->register('nelmio_api_doc.model_describers.form', FormModelDescriber::class)
                ->setPublic(false)
                ->addArgument(new Reference('form.factory'))
                ->addArgument(new Reference('annotations.reader'))
                ->addArgument($container->getParameter('nelmio_api_doc.media_types'))
                ->addTag('nelmio_api_doc.model_describer', ['priority' => 100]);
        }

        $container->getParameterBag()->remove('nelmio_api_doc.media_types');
    }
}
