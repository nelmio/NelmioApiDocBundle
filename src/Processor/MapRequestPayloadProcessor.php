<?php

declare(strict_types=1);

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Processor;

use Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber\SymfonyMapRequestPayloadDescriber;
use OpenApi\Analysis;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

/**
 * A processor that adds query parameters to operations that have a MapRequestPayload attribute.
 * A processor is used to ensure that a Model has been created.
 *
 * @see SymfonyMapRequestPayloadDescriber
 * @deprecated
 */
final class MapRequestPayloadProcessor
{
    public function __invoke(Analysis $analysis): void
    {
    }
}
