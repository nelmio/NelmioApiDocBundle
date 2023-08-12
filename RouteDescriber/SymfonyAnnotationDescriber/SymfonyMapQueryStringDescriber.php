<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\SymfonyAnnotationDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use ReflectionClass;
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
            $constructorParameter = $this->getConstructorReflectionParameterForProperty($parameter, $property);

            $operationParameter = Util::getOperationParameter($operation, $property->property, 'query');
            $this->copyPropertyValuesToQuery($operationParameter, $property);

            $isQueryOptional = (Generator::UNDEFINED !== $property->nullable && $property->nullable)
                || $constructorParameter?->isDefaultValueAvailable()
                || $isModelOptional;

            if (Generator::UNDEFINED === $operationParameter->allowEmptyValue) {
                $operationParameter->allowEmptyValue = $isQueryOptional;
            }

            if (Generator::UNDEFINED === $operationParameter->required) {
                $operationParameter->required = !$isQueryOptional;
            }

            if (Generator::UNDEFINED === $operationParameter->example && $constructorParameter?->isDefaultValueAvailable()) {
                $operationParameter->example = $constructorParameter->getDefaultValue();
            }
        }
    }

    private function getConstructorReflectionParameterForProperty(ReflectionParameter $parameter, OA\Property $property): ?ReflectionParameter
    {
        $reflectionClass = new ReflectionClass($parameter->getType()->getName());

        if (!$contructor = $reflectionClass->getConstructor()) {
            return null;
        }

        foreach ($contructor->getParameters() as $parameter) {
            if ($property->property === $parameter->getName()) {
                return $parameter;
            }
        }

        return null;
    }

    private function copyPropertyValuesToQuery(OA\Parameter $parameter, OA\Property $property): void
    {
        $parameter->schema = Util::getChild($parameter, OA\Schema::class);
        $parameter->schema->ref = $property->ref;
        $parameter->schema->title = $property->title;
        $parameter->schema->description = $property->description;
        $parameter->schema->type = $property->type;
        $parameter->schema->items = $property->items;
        $parameter->schema->example = $property->example;
        $parameter->schema->nullable = $property->nullable;
        $parameter->schema->enum = $property->enum;
        $parameter->schema->default = $property->default;
        $parameter->schema->minimum = $property->minimum;
        $parameter->schema->exclusiveMinimum = $property->exclusiveMinimum;
        $parameter->schema->maximum = $property->maximum;
        $parameter->schema->exclusiveMaximum = $property->exclusiveMaximum;
        $parameter->schema->required = $property->required;
        $parameter->schema->deprecated = $property->deprecated;

        $parameter->name = $property->property;
        $parameter->description = $parameter->schema->description;
        $parameter->required = $parameter->schema->required;
        $parameter->deprecated = $parameter->schema->deprecated;
        $parameter->example = $parameter->schema->example;
    }
}
