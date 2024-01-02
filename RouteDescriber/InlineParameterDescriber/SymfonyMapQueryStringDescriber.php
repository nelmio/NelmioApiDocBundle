<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\InlineParameterDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\PropertyInfo\Type;

final class SymfonyMapQueryStringDescriber implements InlineParameterDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    public function supports(ArgumentMetadata $argumentMetadata): bool
    {
        if (!$argumentMetadata->getAttributes(MapQueryString::class, ArgumentMetadata::IS_INSTANCEOF)) {
            return false;
        }

        return $argumentMetadata->getType() && class_exists($argumentMetadata->getType());
    }

    public function describe(OA\OpenApi $api, OA\Operation $operation, ArgumentMetadata $argumentMetadata): void
    {
        $model = new Model(new Type(Type::BUILTIN_TYPE_OBJECT, $argumentMetadata->isNullable(), $argumentMetadata->getType()));

        $modelRef = $this->modelRegistry->register($model);
        $this->modelRegistry->registerSchemas($model->getHash());

        $nativeModelName = str_replace(OA\Components::SCHEMA_REF, '', $modelRef);

        $schemaModel = Util::getSchema($api, $nativeModelName);

        // There are no properties to map to query parameters
        if (Generator::UNDEFINED === $schemaModel->properties) {
            return;
        }

        $isModelOptional = $argumentMetadata->hasDefaultValue() || $argumentMetadata->isNullable();

        foreach ($schemaModel->properties as $property) {
            $operationParameter = Util::getOperationParameter($operation, $property->property, 'query');
            Util::modifyAnnotationValue($operationParameter, 'schema', $property);
            Util::modifyAnnotationValue($operationParameter, 'name', $property->property);
            Util::modifyAnnotationValue($operationParameter, 'description', $property->description);
            Util::modifyAnnotationValue($operationParameter, 'required', $property->required);
            Util::modifyAnnotationValue($operationParameter, 'deprecated', $property->deprecated);
            Util::modifyAnnotationValue($operationParameter, 'example', $property->example);

            if ($isModelOptional) {
                Util::modifyAnnotationValue($operationParameter, 'required', false);
            } elseif (is_array($schemaModel->required) && in_array($property->property, $schemaModel->required, true)) {
                Util::modifyAnnotationValue($operationParameter, 'required', true);
            } else {
                Util::modifyAnnotationValue($operationParameter, 'required', false);
            }
        }
    }
}
