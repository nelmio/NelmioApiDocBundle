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

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;

class NelmioApiDocExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter('nelmio_api_doc.motd.template', $config['motd']['template']);
        $container->setParameter('nelmio_api_doc.api_name', $config['name']);
        $container->setParameter('nelmio_api_doc.sandbox.enabled',  $config['sandbox']['enabled']);
        $container->setParameter('nelmio_api_doc.sandbox.endpoint', $config['sandbox']['endpoint']);
        $container->setParameter('nelmio_api_doc.sandbox.request_format.method', $config['sandbox']['request_format']['method']);
        $container->setParameter('nelmio_api_doc.sandbox.accept_type', $config['sandbox']['accept_type']);
        $container->setParameter('nelmio_api_doc.sandbox.body_format', $config['sandbox']['body_format']);
        $container->setParameter('nelmio_api_doc.sandbox.request_format.default_format', $config['sandbox']['request_format']['default_format']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('formatters.xml');
        $loader->load('services.xml');

        if ($config['request_listener']['enabled']) {
            $container->setParameter('nelmio_api_doc.request_listener.parameter', $config['request_listener']['parameter']);
            $loader->load('request_listener.xml');
        }

        if (isset($config['sandbox']['authentication'])) {
            $container->setParameter('nelmio_api_doc.sandbox.authentication', $config['sandbox']['authentication']);
        }
    }
}
