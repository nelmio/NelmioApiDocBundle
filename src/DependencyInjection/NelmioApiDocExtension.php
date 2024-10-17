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
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Nelmio\ApiDocBundle\ApiDocGenerator;
use Nelmio\ApiDocBundle\Describer\ExternalDocDescriber;
use Nelmio\ApiDocBundle\Describer\OpenApiPhpDescriber;
use Nelmio\ApiDocBundle\Describer\RouteDescriber;
use Nelmio\ApiDocBundle\ModelDescriber\BazingaHateoasModelDescriber;
use Nelmio\ApiDocBundle\ModelDescriber\JMSModelDescriber;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use Nelmio\ApiDocBundle\Processor\MapQueryStringProcessor;
use Nelmio\ApiDocBundle\Processor\MapRequestPayloadProcessor;
use Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber;
use Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber\RouteArgumentDescriberInterface;
use Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber\SymfonyMapQueryParameterDescriber;
use Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber\SymfonyMapQueryStringDescriber;
use Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber\SymfonyMapRequestPayloadDescriber;
use Nelmio\ApiDocBundle\Routing\FilteredRouteCollectionBuilder;
use OpenApi\Generator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\RouteCollection;

final class NelmioApiDocExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('framework', ['property_info' => ['enabled' => true]]);

        $bundles = $container->getParameter('kernel.bundles');

        // JMS Serializer support
        if (isset($bundles['JMSSerializerBundle'])) {
            $container->prependExtensionConfig('nelmio_api_doc', ['models' => ['use_jms' => true]]);
        }
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../config'));

        $loader->load('services.xml');

        // Filter routes
        $routesDefinition = (new Definition(RouteCollection::class))
            ->setFactory([new Reference('router'), 'getRouteCollection']);

        $container->setParameter('nelmio_api_doc.areas', array_keys($config['areas']));
        $container->setParameter('nelmio_api_doc.media_types', $config['media_types']);
        $container->setParameter('nelmio_api_doc.use_validation_groups', $config['use_validation_groups']);

        // Register the OpenAPI Generator as a service.
        $container->register('nelmio_api_doc.open_api.generator', Generator::class)
            ->setPublic(false);

        $cachePool = $config['cache']['pool'] ?? null;
        $cacheItemId = $config['cache']['item_id'] ?? 'openapi_doc';

        foreach ($config['areas'] as $area => $areaConfig) {
            $areaCachePool = $areaConfig['cache']['pool'] ?? $cachePool;
            $areaCacheItemId = $areaConfig['cache']['item_id'] ?? sprintf('%s.%s', $cacheItemId, $area);

            $nameAliases = $this->findNameAliases($config['models']['names'], $area);
            $container->register(sprintf('nelmio_api_doc.generator.%s', $area), ApiDocGenerator::class)
                ->setPublic(true)
                ->addMethodCall('setAlternativeNames', [$nameAliases])
                ->addMethodCall('setMediaTypes', [$config['media_types']])
                ->addMethodCall('setLogger', [new Reference('logger')])
                ->addMethodCall('setOpenApiVersion', [$config['documentation']['openapi'] ?? null])
                ->addTag('monolog.logger', ['channel' => 'nelmio_api_doc'])
                ->setArguments([
                    new TaggedIteratorArgument(sprintf('nelmio_api_doc.describer.%s', $area)),
                    new TaggedIteratorArgument('nelmio_api_doc.model_describer'),
                    null !== $areaCachePool ? new Reference($areaCachePool) : null,
                    $areaCacheItemId,
                    new Reference('nelmio_api_doc.open_api.generator'),
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
                    new Reference('annotations.reader', ContainerInterface::NULL_ON_INVALID_REFERENCE), // We cannot use the cached version of the annotation reader since the construction of the annotations is context dependant...
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
                                    new Reference('annotation_reader', ContainerInterface::NULL_ON_INVALID_REFERENCE), // Here we use the cached version as we don't deal with @OA annotations in this service
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

        // Add autoconfiguration for model describer
        $container->registerForAutoconfiguration(ModelDescriberInterface::class)
            ->addTag('nelmio_api_doc.model_describer');

        if (!class_exists(\Symfony\Component\Uid\AbstractUid::class)) {
            $container->removeDefinition('nelmio_api_doc.object_model.property_describers.uuid');
        }

        // Import services needed for each library
        $loader->load('php_doc.xml');

        if (interface_exists(ParamInterface::class)) {
            $loader->load('fos_rest.xml');
            $container->getDefinition('nelmio_api_doc.route_describers.fos_rest')
                ->setArgument(1, $config['media_types']);
        }

        if (PHP_VERSION_ID > 80100) {
            // Add autoconfiguration for route argument describer
            $container->registerForAutoconfiguration(RouteArgumentDescriberInterface::class)
                ->addTag('nelmio_api_doc.route_argument_describer');

            $container->register('nelmio_api_doc.route_describers.route_argument', RouteArgumentDescriber::class)
                ->setPublic(false)
                ->addTag('nelmio_api_doc.route_describer', ['priority' => -225])
                ->setArguments([
                    new Reference('argument_metadata_factory'),
                    new TaggedIteratorArgument('nelmio_api_doc.route_argument_describer'),
                ])
            ;

            if (class_exists(MapQueryString::class)) {
                $container->register('nelmio_api_doc.route_argument_describer.map_query_string', SymfonyMapQueryStringDescriber::class)
                    ->setPublic(false)
                    ->addTag('nelmio_api_doc.route_argument_describer', ['priority' => 0]);

                $container->register('nelmio_api_doc.swagger.processor.map_query_string', MapQueryStringProcessor::class)
                    ->setPublic(false)
                    ->addTag('nelmio_api_doc.swagger.processor', ['priority' => 0]);
            }

            if (class_exists(MapRequestPayload::class)) {
                $container->register('nelmio_api_doc.route_argument_describer.map_request_payload', SymfonyMapRequestPayloadDescriber::class)
                    ->setPublic(false)
                    ->addTag('nelmio_api_doc.route_argument_describer', ['priority' => 0]);

                $container->register('nelmio_api_doc.swagger.processor.map_request_payload', MapRequestPayloadProcessor::class)
                    ->setPublic(false)
                    ->addTag('nelmio_api_doc.swagger.processor', ['priority' => 0]);
            }

            if (class_exists(MapQueryParameter::class)) {
                $container->register('nelmio_api_doc.route_argument_describer.map_query_parameter', SymfonyMapQueryParameterDescriber::class)
                    ->setPublic(false)
                    ->addTag('nelmio_api_doc.route_argument_describer', ['priority' => 0]);
            }
        }

        $bundles = $container->getParameter('kernel.bundles');
        if (!isset($bundles['TwigBundle']) || !class_exists('Symfony\Component\Asset\Packages')) {
            $container->removeDefinition('nelmio_api_doc.controller.swagger_ui');

            $container->removeDefinition('nelmio_api_doc.render_docs.html');
            $container->removeDefinition('nelmio_api_doc.render_docs.html.asset');
        } elseif (isset($config['html_config'])) {
            $container->getDefinition('nelmio_api_doc.render_docs.html')->replaceArgument(1, $config['html_config']);
        }

        // ApiPlatform support
        if (isset($bundles['ApiPlatformBundle']) && class_exists('ApiPlatform\Documentation\Documentation')) {
            $loader->load('api_platform.xml');
        }

        // JMS metadata support
        if ($config['models']['use_jms']) {
            $jmsNamingStrategy = interface_exists(SerializationVisitorInterface::class) ? null : new Reference('jms_serializer.naming_strategy');
            $contextFactory = interface_exists(SerializationContextFactoryInterface::class) ? new Reference('jms_serializer.serialization_context_factory') : null;

            $container->register('nelmio_api_doc.model_describers.jms', JMSModelDescriber::class)
                ->setPublic(false)
                ->setArguments([
                    new Reference('jms_serializer.metadata_factory'),
                    new Reference('annotations.reader', ContainerInterface::NULL_ON_INVALID_REFERENCE),
                    $config['media_types'],
                    $jmsNamingStrategy,
                    $container->getParameter('nelmio_api_doc.use_validation_groups'),
                    $contextFactory,
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
    }

    /**
     * @param array<int, mixed> $names
     *
     * @return array<string, mixed>
     */
    private function findNameAliases(array $names, string $area): array
    {
        $nameAliases = array_filter($names, function (array $aliasInfo) use ($area) {
            return [] === $aliasInfo['areas'] || in_array($area, $aliasInfo['areas'], true);
        });

        $aliases = [];
        foreach ($nameAliases as $nameAlias) {
            $aliases[$nameAlias['alias']] = [
                'type' => $nameAlias['type'],
                'groups' => $nameAlias['groups'],
                'options' => $nameAlias['options'],
                'serializationContext' => $nameAlias['serializationContext'],
            ];
        }

        return $aliases;
    }
}
