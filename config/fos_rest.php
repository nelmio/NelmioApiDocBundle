<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $container->services()

        ->set('nelmio_api_doc.route_describers.fos_rest', \Nelmio\ApiDocBundle\RouteDescriber\FosRestDescriber::class)
            ->private()
            ->args([
                abstract_arg('should be set to the configured media types'),
            ])
            ->tag('nelmio_api_doc.route_describer', ['priority' => -250])
    ;
};
