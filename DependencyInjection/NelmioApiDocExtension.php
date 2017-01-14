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
use phpDocumentor\Reflection\DocBlockFactory;
use Swagger\Annotations\Swagger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class NelmioApiDocExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.xml');

        // Filter routes
        $routeCollectionBuilder = $container->getDefinition('nelmio_api_doc.describers.route.filtered_route_collection_builder');
        $routeCollectionBuilder->replaceArgument(0, $config['routes']['path_patterns']);

        // Import services needed for each library
        if (class_exists(DocBlockFactory::class)) {
            $loader->load('php_doc.xml');
        }
        if (class_exists(Swagger::class)) {
            $loader->load('swagger_php.xml');
        }
        if (interface_exists(ParamInterface::class)) {
            $loader->load('fos_rest.xml');
        }

        $bundles = $container->getParameter('kernel.bundles');
        // ApiPlatform support
        if (isset($bundles['ApiPlatformBundle']) && class_exists('ApiPlatform\Core\Documentation\Documentation')) {
            $loader->load('api_platform.xml');
        }
    }
}
