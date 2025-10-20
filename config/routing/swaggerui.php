<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\XmlFileLoader;

return function (RoutingConfigurator $routes): void {
    foreach (debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT) as $trace) {
        if (isset($trace['object']) && $trace['object'] instanceof XmlFileLoader && 'doImport' === $trace['function']) {
            if (__DIR__ === dirname(realpath($trace['args'][3]))) {
                trigger_deprecation('nelmio/api-doc-bundle', '5.6.4', 'The "swaggerui.xml" routing configuration file is deprecated, import "swaggerui.php" instead.');

                break;
            }
        }
    }

    $routes->add('nelmio_api_doc.swagger_ui', '/')
        ->controller('nelmio_api_doc.controller.swagger_ui')
        ->methods(['GET']);
};
