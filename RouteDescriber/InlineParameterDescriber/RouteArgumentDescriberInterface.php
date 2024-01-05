<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\InlineParameterDescriber;

use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

interface RouteArgumentDescriberInterface
{
    public function describe(ArgumentMetadata $argumentMetadata, OA\Operation $operation): void;
}
