<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\SymfonyAnnotationDescriber;

use ReflectionParameter;
use OpenApi\Annotations as OA;

interface SymfonyAnnotationDescriber
{
    public function supports(ReflectionParameter $parameter): bool;
    public function describe(OA\OpenApi $api, OA\Operation $operation, ReflectionParameter $parameter): void;
}
