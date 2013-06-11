<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;


class ParserHandlerCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $handlers = array();
        foreach ($container->findTaggedServiceIds('nelmio_api_doc.parser.handler') as $id => $attributes) {

            // Adding handlers from tagged services
            $handlers[] = new Reference($id);
        }

        foreach($container->findTaggedServiceIds('nelmio_api_doc.extractor.parser') as $id => $attributes) {
            $definition = $container->getDefinition($id);
            $definition->addMethodCall('setHandlers', array($handlers));
        }
    }
}
