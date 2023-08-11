<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber;

use Nelmio\ApiDocBundle\RouteDescriber\SymfonyAnnotationDescriber\SymfonyAnnotationDescriber;
use OpenApi\Annotations as OA;
use ReflectionMethod;
use ReflectionParameter;
use Symfony\Component\Routing\Route;

final class SymfonyDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    /**
     * @param SymfonyAnnotationDescriber[] $annotationDescribers
     */
    public function __construct(
        private iterable $annotationDescribers = [],
    ) {
    }

    public function describe(OA\OpenApi $api, Route $route, ReflectionMethod $reflectionMethod): void
    {
        $parameters = $this->getMethodParameter($reflectionMethod);

        foreach ($this->getOperations($api, $route) as $operation) {
            foreach ($parameters as $parameter) {
                foreach ($this->annotationDescribers as $annotationDescriber) {
                    if (! $annotationDescriber->supports($parameter)) {
                        continue;
                    }

                    $annotationDescriber->describe($api, $operation, $parameter);
                }
            }
        }
    }

    /**
     * @return ReflectionParameter[]
     */
    private function getMethodParameter(ReflectionMethod $reflectionMethod,): array
    {
        $parameters = [];

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $parameters[] = $parameter;
        }

        return $parameters;
    }
}
