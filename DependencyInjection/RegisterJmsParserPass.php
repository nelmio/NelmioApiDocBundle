<?php

namespace Nelmio\ApiDocBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class RegisterJmsParserPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        //JMS may or may not be installed, if it is, load that config as well
        if ($container->hasDefinition('jms_serializer.serializer')) {
            $loader->load('services.jms.xml');
        }
    }
}
