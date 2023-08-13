<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\SymfonyAnnotationDescriber;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use ReflectionParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

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

        $operationParameter = Util::getOperationParameter($operation, $attribute->name ?? $parameter->getName(), 'query');

        SymfonyAnnotationHelper::modifyAnnotationValue($operationParameter, 'allowEmptyValue', $parameter->allowsNull());
        SymfonyAnnotationHelper::modifyAnnotationValue($operationParameter, 'required', !$parameter->isDefaultValueAvailable() && !$parameter->allowsNull());

        /** @var OA\Schema $schema */
        $schema = Util::getChild($operationParameter, OA\Schema::class);

        if (FILTER_VALIDATE_REGEXP === $attribute->filter) {
            SymfonyAnnotationHelper::modifyAnnotationValue($schema, 'pattern', $attribute->options['regexp']);
        }

        SymfonyAnnotationHelper::describeCommonSchemaFromParameter($schema, $parameter);
    }
}
