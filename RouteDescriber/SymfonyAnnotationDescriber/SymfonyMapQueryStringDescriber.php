<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\SymfonyAnnotationDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
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

    /**
     * @param ModelDescriberInterface[] $modelDescribers
     */
    public function __construct(
        private iterable $modelDescribers,
    ) {
    }

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

        $nativeModelName = str_replace(OA\Components::SCHEMA_REF, '', $modelRef);

        $schemaModel = Util::getSchema($api, $nativeModelName);

        foreach ($this->modelDescribers as $modelDescriber) {
            if ($modelDescriber instanceof ModelRegistryAwareInterface) {
                $modelDescriber->setModelRegistry($this->modelRegistry);
            }

            if ($modelDescriber->supports($model)) {
                $modelDescriber->describe($model, $schemaModel);

                break;
            }
        }

        // There are no properties to map to query parameters
        if (Generator::UNDEFINED === $schemaModel->properties) {
            return;
        }

        $isModelOptional = $parameter->isDefaultValueAvailable() || $parameter->allowsNull();

        foreach ($schemaModel->properties as $property) {
            $constructorParameter = $this->getConstructorReflectionParameterForProperty($parameter, $property);

            $operationParameter = Util::getOperationParameter($operation, $property->property, 'query');
            $this->addParameterValuesFromProperty($operationParameter, $property);

            $isQueryOptional = (Generator::UNDEFINED !== $property->nullable && $property->nullable)
                || $constructorParameter?->isDefaultValueAvailable()
                || $isModelOptional;

            $this->overwriteParameterValue($operationParameter, 'required', !$isQueryOptional);

            if ($constructorParameter?->isDefaultValueAvailable()) {
                $this->overwriteParameterValue($operationParameter, 'example', $constructorParameter->getDefaultValue());
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

    private function addParameterValuesFromProperty(OA\Parameter $parameter, OA\Property $property): void
    {
        $this->overwriteParameterValue($parameter, 'schema', $property);
        $this->overwriteParameterValue($parameter, 'name', $property->property);
        $this->overwriteParameterValue($parameter, 'description', $property->description);
        $this->overwriteParameterValue($parameter, 'required', $property->required);
        $this->overwriteParameterValue($parameter, 'deprecated', $property->deprecated);
        $this->overwriteParameterValue($parameter, 'example', $property->example);
    }

    private function overwriteParameterValue(OA\Parameter $parameter, string $property, $value): void
    {
        if (!Generator::isDefault($parameter->{$property})) {
            return;
        }

        $parameter->{$property} = $value;
    }
}
