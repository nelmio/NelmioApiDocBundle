<?php

namespace Nelmio\ApiDocBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class RegisterExtractorClassParsersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('nelmio_api_doc.extractor.api_doc_extractor')) {
            return;
        }

        $definition = $container->getDefinition('nelmio_api_doc.extractor.api_doc_extractor');

        foreach ($container->findTaggedServiceIds('nelmio_api_doc.extractor.class_parser') as $id => $attributes) {
            $definition->addMethodCall('registerParser', array(new Reference($id)));
        }
    }
}