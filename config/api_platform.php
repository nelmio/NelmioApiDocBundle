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

        ->set('nelmio_api_doc.describers.api_platform', \Nelmio\ApiDocBundle\Describer\ApiPlatformDescriber::class)
            ->private()
            ->args([
                service('nelmio_api_doc.describers.api_platform.openapi'),
                service('api_platform.openapi.normalizer'),
            ])
            ->tag('nelmio_api_doc.describer', ['priority' => -100])

        ->set('nelmio_api_doc.describers.api_platform.openapi', \ApiPlatform\OpenApi\OpenApi::class)
            ->private()
            ->factory([service('api_platform.openapi.factory'), '__invoke'])

    ;
};
