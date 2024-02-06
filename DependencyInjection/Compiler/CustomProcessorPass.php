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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler Pass to identify and register custom processors.
 *  *
 * @internal
 */
final class CustomProcessorPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * Process services tagged as 'swagger.processor'.
     *
     * @param ContainerBuilder $container The container builder
     */
    public function process(ContainerBuilder $container): void
    {
        // Find the OpenAPI generator service.
        $definition = $container->findDefinition('nelmio_api_doc.open_api.generator');

        foreach ($this->findAndSortTaggedServices('nelmio_api_doc.swagger.processor', $container) as $reference) {
            $id = (string) $reference;
            $tags = $container->findDefinition($id)->getTags();

            /**
             * Before which processor should this processor be run?
             *
             * @var string|null
             */
            $before = null;

            // See if the processor has a 'before' attribute.
            foreach ($tags as $tag) {
                if (isset($tag['before'])) {
                    $before = $tag['before'];
                }
            }

            $definition->addMethodCall('addProcessor', [new Reference($id), $before]);
        }
    }
}
