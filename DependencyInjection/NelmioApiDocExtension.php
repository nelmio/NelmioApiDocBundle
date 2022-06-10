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
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Nelmio\ApiDocBundle\ApiDocGenerator;
use Nelmio\ApiDocBundle\Describer\ExternalDocDescriber;
use Nelmio\ApiDocBundle\Describer\OpenApiPhpDescriber;
use Nelmio\ApiDocBundle\Describer\RouteDescriber;
use Nelmio\ApiDocBundle\ModelDescriber\BazingaHateoasModelDescriber;
use Nelmio\ApiDocBundle\ModelDescriber\JMSModelDescriber;
use Nelmio\ApiDocBundle\Routing\FilteredRouteCollectionBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;
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

        $bundles = $container->getParameter('kernel.bundles');

        // JMS Serializer support
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
        $container->setParameter('nelmio_api_doc.media_types', $config['media_types']);
        foreach ($config['areas'] as $area => $areaConfig) {
            $nameAliases = $this->findNameAliases($config['models']['names'], $area);
            $container->register(sprintf('nelmio_api_doc.generator.%s', $area), ApiDocGenerator::class)
                ->setPublic(true)
                ->addMethodCall('setAlternativeNames', [$nameAliases])
                ->addMethodCall('setMediaTypes', [$config['media_types']])
                ->addMethodCall('setLogger', [new Reference('logger')])
                ->addTag('monolog.logger', ['channel' => 'nelmio_api_doc'])
                ->setArguments([
                    new TaggedIteratorArgument(sprintf('nelmio_api_doc.describer.%s', $area)),
                    new TaggedIteratorArgument('nelmio_api_doc.model_describer'),
                ]);

            $container->register(sprintf('nelmio_api_doc.describers.route.%s', $area), RouteDescriber::class)
                ->setPublic(false)
                ->setArguments([
                    new Reference(sprintf('nelmio_api_doc.routes.%s', $area)),
                    new Reference('nelmio_api_doc.controller_reflector'),
                    new TaggedIteratorArgument('nelmio_api_doc.route_describer'),
                ])
                ->addTag(sprintf('nelmio_api_doc.describer.%s', $area), ['priority' => -400]);

            $container->register(sprintf('nelmio_api_doc.describers.openapi_php.%s', $area), OpenApiPhpDescriber::class)
                ->setPublic(false)
                ->setArguments([
                    new Reference(sprintf('nelmio_api_doc.routes.%s', $area)),
                    new Reference('nelmio_api_doc.controller_reflector'),
                    new Reference('annotations.reader'), // We cannot use the cached version of the annotation reader since the construction of the annotations is context dependant...
                    new Reference('logger'),
                ])
                ->addTag(sprintf('nelmio_api_doc.describer.%s', $area), ['priority' => -200]);

            $container->register(sprintf('nelmio_api_doc.describers.config.%s', $area), ExternalDocDescriber::class)
                ->setPublic(false)
                ->setArguments([
                    $areaConfig['documentation'],
                    true,
                ])
                ->addTag(sprintf('nelmio_api_doc.describer.%s', $area), ['priority' => 990]);

            unset($areaConfig['documentation']);
            if (0 === count($areaConfig['path_patterns'])
                && 0 === count($areaConfig['host_patterns'])
                && 0 === count($areaConfig['name_patterns'])
                && false === $areaConfig['with_annotation']
                && false === $areaConfig['disable_default_routes']
            ) {
                $container->setDefinition(sprintf('nelmio_api_doc.routes.%s', $area), $routesDefinition)
                    ->setPublic(false);
            } else {
                $container->register(sprintf('nelmio_api_doc.routes.%s', $area), RouteCollection::class)
                    ->setPublic(false)
                    ->setFactory([
                        (new Definition(FilteredRouteCollectionBuilder::class))
                            ->setArguments(
                                [
                                    new Reference('annotation_reader'), // Here we use the cached version as we don't deal with @OA annotations in this service
                                    new Reference('nelmio_api_doc.controller_reflector'),
                                    $area,
                                    $areaConfig,
                                ]
                            ),
                        'filter',
                    ])
                    ->addArgument($routesDefinition);
            }
        }

        $container->register('nelmio_api_doc.generator_locator', ServiceLocator::class)
            ->setPublic(false)
            ->addTag('container.service_locator')
            ->addArgument(array_combine(
                array_keys($config['areas']),
                array_map(function ($area) { return new Reference(sprintf('nelmio_api_doc.generator.%s', $area)); }, array_keys($config['areas']))
            ));

        $container->getDefinition('nelmio_api_doc.model_describers.object')
            ->setArgument(3, $config['media_types']);

        // Import services needed for each library
        $loader->load('php_doc.xml');

        if (interface_exists(ParamInterface::class)) {
            $loader->load('fos_rest.xml');
            $container->getDefinition('nelmio_api_doc.route_describers.fos_rest')
                ->setArgument(1, $config['media_types']);
        }

        $bundles = $container->getParameter('kernel.bundles');
        if (!isset($bundles['TwigBundle']) || !class_exists('Symfony\Component\Asset\Packages')) {
            $container->removeDefinition('nelmio_api_doc.controller.swagger_ui');

            $container->removeDefinition('nelmio_api_doc.render_docs.html');
            $container->removeDefinition('nelmio_api_doc.render_docs.html.asset');
        }

        // ApiPlatform support
        if (isset($bundles['ApiPlatformBundle']) && class_exists('ApiPlatform\Core\Documentation\Documentation')) {
            $loader->load('api_platform.xml');
        }

        // JMS metadata support
        if ($config['models']['use_jms']) {
            $jmsNamingStrategy = interface_exists(SerializationVisitorInterface::class) ? null : new Reference('jms_serializer.naming_strategy');

            $container->register('nelmio_api_doc.model_describers.jms', JMSModelDescriber::class)
                ->setPublic(false)
                ->setArguments([
                    new Reference('jms_serializer.metadata_factory'),
                    new Reference('annotations.reader'),
                    $config['media_types'],
                    $jmsNamingStrategy,
                ])
                ->addTag('nelmio_api_doc.model_describer', ['priority' => 50]);

            // Bazinga Hateoas metadata support
            if (isset($bundles['BazingaHateoasBundle'])) {
                $container->register('nelmio_api_doc.model_describers.jms.bazinga_hateoas', BazingaHateoasModelDescriber::class)
                    ->setDecoratedService('nelmio_api_doc.model_describers.jms', 'nelmio_api_doc.model_describers.jms.inner')
                    ->setPublic(false)
                    ->setArguments([
                        new Reference('hateoas.configuration.metadata_factory'),
                        new Reference('nelmio_api_doc.model_describers.jms.inner'),
                    ]);
            }
        } else {
            $container->removeDefinition('nelmio_api_doc.model_describers.object_fallback');
        }

        // Import the base configuration
        $container->getDefinition('nelmio_api_doc.describers.config')->replaceArgument(0, $config['documentation']);

        // Compatibility Symfony
        $controllerNameConverter = null;
        if ($container->hasDefinition('.legacy_controller_name_converter')) { // 4.4
            $controllerNameConverter = $container->getDefinition('.legacy_controller_name_converter');
        } elseif ($container->hasDefinition('controller_name_converter')) { // < 4.4
            $controllerNameConverter = $container->getDefinition('controller_name_converter');
        }

        if (null !== $controllerNameConverter) {
            $container->getDefinition('nelmio_api_doc.controller_reflector')->setArgument(1, $controllerNameConverter);
        }
    }

    private function findNameAliases(array $names, string $area): array
    {
        $nameAliases = array_filter($names, function (array $aliasInfo) use ($area) {
            return empty($aliasInfo['areas']) || in_array($area, $aliasInfo['areas'], true);
        });

        $aliases = [];
        foreach ($nameAliases as $nameAlias) {
            $aliases[$nameAlias['alias']] = [
                'type' => $nameAlias['type'],
                'groups' => $nameAlias['groups'],
            ];
        }

        return $aliases;
    }
}
