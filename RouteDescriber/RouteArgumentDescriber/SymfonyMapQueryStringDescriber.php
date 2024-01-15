<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Validator\Constraints\GroupSequence;

final class SymfonyMapQueryStringDescriber implements RouteArgumentDescriberInterface, ModelRegistryAwareInterface
{
    public const CONTEXT_ARGUMENT_METADATA = 'nelmio_api_doc_bundle.argument_metadata.'.self::class;
    public const CONTEXT_MODEL_REF = 'nelmio_api_doc_bundle.model_ref.'.self::class;

    use ModelRegistryAwareTrait;

    public function describe(ArgumentMetadata $argumentMetadata, OA\Operation $operation): void
    {
        /** @var MapQueryString $attribute */
        if (!$attribute = $argumentMetadata->getAttributes(MapQueryString::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null) {
            return;
        }

        $modelRef = $this->modelRegistry->register(new Model(
            new Type(Type::BUILTIN_TYPE_OBJECT, $argumentMetadata->isNullable(), $argumentMetadata->getType()),
            groups: $this->getGroups($attribute),
            serializationContext: $attribute->serializationContext,
        ));

        $operation->_context->{self::CONTEXT_ARGUMENT_METADATA} = $argumentMetadata;
        $operation->_context->{self::CONTEXT_MODEL_REF} = $modelRef;
    }

    /**
     * @return string[]|null
     */
    private function getGroups(MapQueryString $attribute): ?array
    {
        if (null === $attribute->validationGroups) {
            return null;
        }

        if (is_string($attribute->validationGroups)) {
            return [$attribute->validationGroups];
        }

        if (is_array($attribute->validationGroups)) {
            return $attribute->validationGroups;
        }

        if ($attribute->validationGroups instanceof GroupSequence) {
            return $attribute->validationGroups->groups;
        }

        return null;
    }
}
