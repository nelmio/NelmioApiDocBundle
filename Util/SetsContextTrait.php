<?php

namespace Nelmio\ApiDocBundle\Util;

use OpenApi\Analyser;
use OpenApi\Context;
use OpenApi\Generator;

/**
 * @internal
 */
trait SetsContextTrait
{
    private function setContext(?Context $context): void
    {
        if (class_exists(Analyser::class)) {
            // zircote/swagger-php ^3.2
            Analyser::$context = $context;
        } else {
            /// zircote/swagger-php ^4.0
            Generator::$context = $context;
        }
    }
}
