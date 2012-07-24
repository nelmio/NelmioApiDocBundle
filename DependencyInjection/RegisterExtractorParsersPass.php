<?php

namespace Nelmio\ApiDocBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class RegisterExtractorParsersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('nelmio_api_doc.extractor.api_doc_extractor')) {
            return;
        }

        $definition = $container->getDefinition('nelmio_api_doc.extractor.api_doc_extractor');

        //find registered parsers and sort by priority
        $sortedParsers = array();
        foreach ($container->findTaggedServiceIds('nelmio_api_doc.extractor.parser') as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $priority = isset($attributes['priority']) ? $attributes['priority'] : 0;
                $sortedParsers[$priority][] = $id;
            }
        }

        //add parsers if any
        if (!empty($sortedParsers)) {
            krsort($sortedParsers);
            $sortedParsers = call_user_func_array('array_merge', $sortedParsers);

            //add method call for each registered parsers
            foreach ($sortedParsers as $id) {
                $definition->addMethodCall('addParser', array(new Reference($id)));
            }
        }
    }
}
