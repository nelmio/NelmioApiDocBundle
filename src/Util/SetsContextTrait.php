<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Util;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
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

    /**
     * @param \ReflectionClass|\ReflectionProperty|\ReflectionMethod $reflection
     */
    private function setContextFromReflection(Context $parentContext, $reflection): void
    {
        // In order to have nicer errors
        if ($reflection instanceof \ReflectionClass) {
            $this->setContext(Util::createWeakContext($parentContext, [
                'namespace' => $reflection->getNamespaceName(),
                'class' => $reflection->getShortName(),
                'filename' => $reflection->getFileName(),
            ]));
        } else {
            $declaringClass = $reflection->getDeclaringClass();

            $this->setContext(Util::createWeakContext($parentContext, [
                'namespace' => $declaringClass->getNamespaceName(),
                'class' => $declaringClass->getShortName(),
                'property' => $reflection->name,
                'filename' => $declaringClass->getFileName(),
            ]));
        }
    }
}
