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

use Nelmio\ApiDocBundle\ApiDocGenerator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler Pass to identify and register custom processors.
 *  *
 * @internal
 */
final class CustomProcessorPass implements CompilerPassInterface
{
    /**
     * Process services tagged as 'swagger.processor'.
     *
     * @param ContainerBuilder $container The container builder
     *
     * @return void
     */
    public function process(ContainerBuilder $container): void
    {
        $definitions = [];
        // Find all registered generator services.
        foreach($container->getDefinitions() as $id => $definition) {
            // Check if the service-id contains the tag 'nelmio_api_doc.generator'
            if (strpos($id, 'nelmio_api_doc.generator.') === false) {
                continue;
            }

            $definitions[$id] = $definition;
        }

        // If there are no definitions, we can stop here.
        if (empty($definitions)) {
            return;
        }

        foreach ($container->findTaggedServiceIds('swagger.processor') as $id => $tags) {
            $processors[] = $id;
        }

        foreach( $definitions as $definition ) {
            foreach($processors as $processor) {
                $definition->addMethodCall('registerProcessor', [$processor]);
            }
        }
    }
}
