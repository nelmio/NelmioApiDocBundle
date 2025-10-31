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

        ->set('nelmio_api_doc.route_describers.php_doc', \Nelmio\ApiDocBundle\RouteDescriber\PhpDocDescriber::class)
            ->private()
            ->tag('nelmio_api_doc.route_describer', ['priority' => -275])
    ;
};
