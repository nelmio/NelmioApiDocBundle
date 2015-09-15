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
 * Class Swagger2ConfigCompilerPass
 *
 * @author Bez Hermoso <bezalelhermoso@gmail.com>
 */
class Swagger2ConfigCompilerPass implements CompilerPassInterface
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
        $formatter = $container->getDefinition('nelmio_api_doc.formatter.swagger2_formatter');

        $formatter->addMethodCall('setBasePath', array($container->getParameter('nelmio_api_doc.swagger2.base_path')));
        $formatter->addMethodCall('setInfo', array($container->getParameter('nelmio_api_doc.swagger2.info')));
        $formatter->addMethodCall('setConsumes', array($container->getParameter('nelmio_api_doc.swagger2.consumes')));
        $formatter->addMethodCall('setProduces', array($container->getParameter('nelmio_api_doc.swagger2.produces')));
        $formatter->addMethodCall('setSchemes', array($container->getParameter('nelmio_api_doc.swagger2.schemes')));
    }
}
