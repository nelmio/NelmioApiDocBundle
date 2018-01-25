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
use Nelmio\ApiDocBundle\ApiDocGenerator;
use Nelmio\ApiDocBundle\Describer\RouteDescriber;
use Nelmio\ApiDocBundle\Describer\SwaggerPhpDescriber;
use Nelmio\ApiDocBundle\ModelDescriber\JMSModelDescriber;
use Nelmio\ApiDocBundle\Routing\FilteredRouteCollectionBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
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

        // Filter routes
        $routesDefinition = (new Definition(RouteCollection::class))
            ->setFactory([new Reference('router'), 'getRouteCollection']);

        $container->setParameter('nelmio_api_doc.areas', array_keys($config['areas']));
        foreach ($config['areas'] as $area => $areaConfig) {
            $container->register(sprintf('nelmio_api_doc.generator.%s', $area), ApiDocGenerator::class)
                ->setPublic(false)
                ->setArguments([
                    new TaggedIteratorArgument(sprintf('nelmio_api_doc.describer.%s', $area)),
                    new TaggedIteratorArgument('nelmio_api_doc.model_describer'),
                ]);

            if (0 === count($areaConfig['path_patterns'])) {
                $container->setDefinition(sprintf('nelmio_api_doc.routes.%s', $area), $routesDefinition)
                    ->setPublic(false);
            } else {
                $container->register(sprintf('nelmio_api_doc.routes.%s', $area), RouteCollection::class)
                    ->setPublic(false)
                    ->setFactory([
                        (new Definition(FilteredRouteCollectionBuilder::class))
                            ->addArgument($areaConfig['path_patterns']),
                        'filter',
                    ])
                    ->addArgument($routesDefinition);
            }

            $container->register(sprintf('nelmio_api_doc.describers.route.%s', $area), RouteDescriber::class)
                ->setPublic(false)
                ->setArguments([
                    new Reference(sprintf('nelmio_api_doc.routes.%s', $area)),
                    new Reference('nelmio_api_doc.controller_reflector'),
                    new TaggedIteratorArgument('nelmio_api_doc.route_describer'),
                ])
                ->addTag(sprintf('nelmio_api_doc.describer.%s', $area), ['priority' => -400]);

            $container->register(sprintf('nelmio_api_doc.describers.swagger_php.%s', $area), SwaggerPhpDescriber::class)
                ->setPublic(false)
                ->setArguments([
                    new Reference(sprintf('nelmio_api_doc.routes.%s', $area)),
                    new Reference('nelmio_api_doc.controller_reflector'),
                    new Reference('annotation_reader'),
                ])
                ->addTag(sprintf('nelmio_api_doc.describer.%s', $area), ['priority' => -200]);
        }

        $container->register('nelmio_api_doc.generator_locator')
            ->setPublic(false)
            ->addTag('container.service_locator')
            ->addArgument(array_combine(
                array_keys($config['areas']),
                array_map(function ($area) { return new Reference(sprintf('nelmio_api_doc.generator.%s', $area)); }, array_keys($config['areas']))
            ));

        // Import services needed for each library
        $loader->load('php_doc.xml');

        if (interface_exists(ParamInterface::class)) {
            $loader->load('fos_rest.xml');
        }

        // ApiPlatform support
        $bundles = $container->getParameter('kernel.bundles');
        if (!isset($bundles['TwigBundle'])) {
            $container->removeDefinition('nelmio_api_doc.controller.swagger_ui');
        }
        if (isset($bundles['ApiPlatformBundle']) && class_exists('ApiPlatform\Core\Documentation\Documentation')) {
            $loader->load('api_platform.xml');
        }

        // JMS metadata support
        if ($config['models']['use_jms']) {
            $container->register('nelmio_api_doc.model_describers.jms', JMSModelDescriber::class)
                ->setPublic(false)
                ->setArguments([
                    new Reference('jms_serializer.metadata_factory'),
                    new Reference('jms_serializer.naming_strategy'),
                    new Reference('annotation_reader'),
                ])
                ->addTag('nelmio_api_doc.model_describer', ['priority' => 50]);
        }

        // Import the base configuration
        $container->getDefinition('nelmio_api_doc.describers.config')->replaceArgument(0, $config['documentation']);
    }
}
