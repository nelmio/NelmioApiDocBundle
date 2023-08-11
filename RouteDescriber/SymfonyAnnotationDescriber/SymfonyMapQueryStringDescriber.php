<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\SymfonyAnnotationDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use ReflectionParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\PropertyInfo\Type;

final class SymfonyMapQueryStringDescriber implements SymfonyAnnotationDescriber, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    public function supports(ReflectionParameter $parameter): bool
    {
        if (!SymfonyAnnotationHelper::getAttribute($parameter, MapQueryString::class)) {
            return false;
        }

        return $parameter->hasType();
    }

    public function describe(OA\OpenApi $api, OA\Operation $operation, ReflectionParameter $parameter): void
    {
        $model = new Model(new Type(Type::BUILTIN_TYPE_OBJECT, $parameter->allowsNull(), $parameter->getType()->getName()));
        $modelRef = $this->modelRegistry->register($model);
        $this->modelRegistry->registerSchemas();

        $nativeModelName = str_replace(OA\Components::SCHEMA_REF, '', $modelRef);

        $schemaModel = Util::getSchema($api, $nativeModelName);
        if (Generator::UNDEFINED === $schemaModel->properties) {
            return;
        }

        $isModelOptional = $parameter->isDefaultValueAvailable() || $parameter->allowsNull();

        foreach ($schemaModel->properties as $property) {
            $operationParameter = Util::getOperationParameter($operation, $property->property, 'query');
            $operationParameter->name = $property->property;

            $isQueryOptional = (Generator::UNDEFINED !== $property->nullable && $property->nullable)
                || Generator::UNDEFINED !== $property->default
                || $isModelOptional;

            $operationParameter->allowEmptyValue = $isQueryOptional;
            $operationParameter->required = !$isQueryOptional;
            $operationParameter->example = $property->default;
        }
    }
}
