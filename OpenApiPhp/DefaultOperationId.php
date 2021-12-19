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
use OpenApi\Generator;

/**
 * Disable the OperationId processor from zircote/swagger-php as it breaks our documentation by setting non-unique operation ids.
 * See https://github.com/zircote/swagger-php/pull/483#issuecomment-360739260 for the solution used here.
 *
 * @internal
 */
final class DefaultOperationId
{
    public function __invoke(Analysis $analysis)
    {
        $allOperations = $analysis->getAnnotationsOfType(OA\Operation::class);

        foreach ($allOperations as $operation) {
            if (Generator::UNDEFINED === $operation->operationId) {
                $operation->operationId = null;
            }
        }
    }
}
