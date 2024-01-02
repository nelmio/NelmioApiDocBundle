<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\InlineParameterDescriber;

use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

interface InlineParameterDescriberInterface
{
    public function supports(ArgumentMetadata $argumentMetadata): bool;

    public function describe(OA\OpenApi $api, OA\Operation $operation, ArgumentMetadata $argumentMetadata): void;
}
