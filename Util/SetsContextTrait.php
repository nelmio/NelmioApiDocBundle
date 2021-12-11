<?php

namespace Nelmio\ApiDocBundle\Util;

use OpenApi\Context;

/**
 * @internal
 */
trait SetsContextTrait
{
    private function setContext(?Context $context): void
    {
        if (class_exists(\OpenApi\Analyser::class)) {
            // zircote/swagger-php ^3.2
            \OpenApi\Analyser::$context = $context;
        } else {
            /// zircote/swagger-php ^4.0
            \OpenApi\Generator::$context = $context;
        }
    }
}
