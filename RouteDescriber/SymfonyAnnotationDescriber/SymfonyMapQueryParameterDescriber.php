<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\SymfonyAnnotationDescriber;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use ReflectionParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use OpenApi\Annotations as OA;

final class SymfonyMapQueryParameterDescriber implements SymfonyAnnotationDescriber
{
    public function supports(ReflectionParameter $parameter): bool
    {
        if (!SymfonyAnnotationHelper::getAttribute($parameter, MapQueryParameter::class)) {
            return false;
        }

        return $parameter->hasType();
    }

    public function describe(OA\OpenApi $api, OA\Operation $operation, ReflectionParameter $parameter): void
    {
        $attribute = SymfonyAnnotationHelper::getAttribute($parameter, MapQueryParameter::class);

        $operationParameter = Util::getOperationParameter($operation, $parameter->getName(), 'query');
        $operationParameter->name = $attribute->name ?? $parameter->getName();
        $operationParameter->allowEmptyValue = $parameter->allowsNull();

        $operationParameter->required = !$parameter->isDefaultValueAvailable() && !$parameter->allowsNull();

        /** @var OA\Schema $schema */
        $schema = Util::getChild($operationParameter, OA\Schema::class);

        if (FILTER_VALIDATE_REGEXP === $attribute->filter) {
            $schema->pattern = $attribute->options['regexp'];
        }

        SymfonyAnnotationHelper::describeCommonSchemaFromParameter($schema, $parameter);
    }
}
