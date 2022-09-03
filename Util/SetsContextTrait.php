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
        // zircote/swagger-php ^4.0
        \OpenApi\Generator::$context = $context;
    }
}
