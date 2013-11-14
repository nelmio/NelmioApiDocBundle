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

class ExtractorHandlerCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $handlers = array();
        foreach ($container->findTaggedServiceIds('nelmio_api_doc.extractor.handler') as $id => $attributes) {
            $handlers[] = new Reference($id);
        }

        $container
            ->getDefinition('nelmio_api_doc.extractor.api_doc_extractor')
            ->replaceArgument(4, $handlers);
    }
}
