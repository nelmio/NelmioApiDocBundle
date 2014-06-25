<?php

namespace Nelmio\ApiDocBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * Loads parsers to extract information from different libraries.
 *
 * They are only loaded when the corresponding library is installed and enabled.
 */
class LoadExtractorParsersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        // forms may not be installed/enabled, if it is, load that config as well
        if ($container->hasDefinition('form.factory')) {
            $loader->load('services.form.xml');
        }

        // validation may not be installed/enabled, if it is, load that config as well
        if ($container->has('validator.mapping.class_metadata_factory')) {
            $loader->load('services.validation.xml');
        }

        // JMS may or may not be installed, if it is, load that config as well
        if ($container->hasDefinition('jms_serializer.serializer')) {
            $loader->load('services.jms.xml');
        }
    }
}
