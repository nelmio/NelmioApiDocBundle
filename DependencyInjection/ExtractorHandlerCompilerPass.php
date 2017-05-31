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
        $extractorId = 'nelmio_api_doc.extractor.api_doc_extractor';

        if (!$container->hasDefinition($extractorId)) {
            return;
        }

        $handlers = array();
        foreach ($container->findTaggedServiceIds('nelmio_api_doc.extractor.handler') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $handlers[$priority][] = new Reference($id);
        }

        if (empty($handlers)) {
            return;
        }

        // sort by priority and flatten
        krsort($handlers);
        $handlers = call_user_func_array('array_merge', $handlers);

        $container->getDefinition($extractorId)->replaceArgument(5, $handlers);
    }
}
