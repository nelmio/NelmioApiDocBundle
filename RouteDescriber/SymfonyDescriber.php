<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber;

use InvalidArgumentException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\RouteDescriber\SymfonyAnnotationDescriber\SymfonyAnnotationDescriber;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use ReflectionMethod;
use ReflectionParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Routing\Route;
use function is_array;
use const FILTER_VALIDATE_REGEXP;

final class SymfonyDescriber implements RouteDescriberInterface, ModelRegistryAwareInterface
{
    use RouteDescriberTrait;
    use ModelRegistryAwareTrait;

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
