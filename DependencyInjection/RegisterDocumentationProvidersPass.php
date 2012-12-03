<?php

namespace Nelmio\ApiDocBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class RegisterDocumentationProvidersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('nelmio_api_doc.extractor.collector')) {
            return;
        }

        $definition = $container->getDefinition('nelmio_api_doc.extractor.collector');

        //find registered parsers and sort by priority
        $sortedParsers = array();
        foreach ($container->findTaggedServiceIds('nelmio_api_doc.provider') as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                // $priority = isset($attributes['priority']) ? $attributes['priority'] : 0;
                echo "Attribute: ";
                print_r($attributes);
            }

            $definition->addMethodCall('addProvider', array(new Reference($id)));
        }
    }
}