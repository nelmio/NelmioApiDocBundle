<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\DependencyInjection;

use FOS\RestBundle\Controller\Annotations\ParamInterface;
use Nelmio\ApiDocBundle\ModelDescriber\FormModelDescriber;
use Nelmio\ApiDocBundle\ModelDescriber\JMSModelDescriber;
use Nelmio\ApiDocBundle\Routing\FilteredRouteCollectionBuilder;
use phpDocumentor\Reflection\DocBlockFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Routing\RouteCollection;

final class NelmioApiDocExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('framework', ['property_info' => ['enabled' => true]]);

        // JMS Serializer support
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['JMSSerializerBundle'])) {
            $container->prependExtensionConfig('nelmio_api_doc', ['models' => ['use_jms' => true]]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.xml');

        if (interface_exists(FormInterface::class)) {
            $container->register('nelmio_api_doc.model_describers.form', FormModelDescriber::class)
                ->setPublic(false)
                ->addArgument(new Reference('form.factory'))
                ->addTag('nelmio_api_doc.model_describer', ['priority' => 100]);
        }

        // Filter routes
        $routesDefinition = (new Definition(RouteCollection::class))
            ->setFactory([new Reference('router'), 'getRouteCollection']);

        if (0 === count($config['routes']['path_patterns'])) {
            $container->setDefinition('nelmio_api_doc.routes', $routesDefinition)
                ->setPublic(false);
        } else {
            $container->register('nelmio_api_doc.routes', RouteCollection::class)
                ->setPublic(false)
                ->setFactory([
                    (new Definition(FilteredRouteCollectionBuilder::class))
                        ->addArgument($config['routes']['path_patterns']),
                    'filter',
                ])
                ->addArgument($routesDefinition);
        }

        // Import services needed for each library
        $loader->load('swagger_php.xml');
        if (class_exists(DocBlockFactory::class)) {
            $loader->load('php_doc.xml');
        }
        if (interface_exists(ParamInterface::class)) {
            $loader->load('fos_rest.xml');
        }

        // ApiPlatform support
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['ApiPlatformBundle']) && class_exists('ApiPlatform\Core\Documentation\Documentation')) {
            $loader->load('api_platform.xml');
        }

        // JMS metadata support
        if ($config['models']['use_jms']) {
            $container->register('nelmio_api_doc.model_describers.jms', JMSModelDescriber::class)
                ->setPublic(false)
                ->setArguments([new Reference('jms_serializer.metadata_factory'), new Reference('jms_serializer.naming_strategy')])
                ->addTag('nelmio_api_doc.model_describer', ['priority' => 50]);
        }

        // Import the base configuration
        $container->getDefinition('nelmio_api_doc.describers.config')->replaceArgument(0, $config['documentation']);
    }
}
