<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\SwaggerPhp;

use Swagger\Analysis;
use Swagger\Annotations\Info;
use Swagger\Annotations\Swagger;
use Swagger\Context;

/**
 * Add defaults to fix default warnings.
 *
 * @internal
 */
final class AddDefaults
{
    public function __invoke(Analysis $analysis)
    {
        if ($analysis->getAnnotationsOfType(Info::class)) {
            return;
        }
        if (($annotations = $analysis->getAnnotationsOfType(Swagger::class)) && null !== $annotations[0]->info) {
            return;
        }

        $analysis->addAnnotation(new Info(['title' => '', 'version' => '0.0.0', '_context' => new Context(['generated' => true])]), null);
    }
}
