<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass that configures the SwaggerFormatter instance.
 *
 * @author Bez Hermoso <bez@activelamp.com>
 */
class SwaggerConfigCompilerPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $formatter = $container->getDefinition('nelmio_api_doc.formatter.swagger_formatter');

        $formatter->addMethodCall('setBasePath', array($container->getParameter('nelmio_api_doc.swagger.base_path')));
        $formatter->addMethodCall('setApiVersion', array($container->getParameter('nelmio_api_doc.swagger.api_version')));
        $formatter->addMethodCall('setSwaggerVersion', array($container->getParameter('nelmio_api_doc.swagger.swagger_version')));
        $formatter->addMethodCall('setInfo', array($container->getParameter('nelmio_api_doc.swagger.info')));

        $authentication = $container->getParameter('nelmio_api_doc.sandbox.authentication');

        $formatter->setArguments(array(
            $container->getParameter('nelmio_api_doc.swagger.model_naming_strategy'),
        ));

        if ($authentication !== null) {
            $formatter->addMethodCall('setAuthenticationConfig', array($authentication));
        }
    }
}
