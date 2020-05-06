<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\OpenApiPhp;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;

/**
 * Add defaults to fix default warnings.
 *
 * @internal
 */
final class AddDefaults
{
    public function __invoke(Analysis $analysis)
    {
        if ($analysis->getAnnotationsOfType(OA\Info::class)) {
            return;
        }
        if (($annotations = $analysis->getAnnotationsOfType(OA\OpenApi::class)) && OA\UNDEFINED !== $annotations[0]->info) {
            return;
        }
        if (OA\UNDEFINED !== $analysis->openapi->info) {
            return;
        }

        $analysis->addAnnotation(new OA\Info(['title' => '', 'version' => '0.0.0', '_context' => new Context(['generated' => true])]), null);
    }
}
