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

final class SymfonyMapQueryStringDescriber implements RouteArgumentDescriberInterface, ModelRegistryAwareInterface
{
    public const CONTEXT_ARGUMENT_METADATA = 'nelmio_api_doc_bundle.map_query_string.argument_metadata';
    public const CONTEXT_MODEL_REF = 'nelmio_api_doc_bundle.map_query_string.model_ref';

    use ModelRegistryAwareTrait;

    public function describe(ArgumentMetadata $argumentMetadata, OA\Operation $operation): void
    {
        /** @var MapQueryString $attribute */
        if (!$attribute = $argumentMetadata->getAttributes(MapQueryString::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null) {
            return;
        }

        $modelRef = $this->modelRegistry->register(new Model(
            new Type(Type::BUILTIN_TYPE_OBJECT, $argumentMetadata->isNullable(), $argumentMetadata->getType()),
            serializationContext: $attribute->serializationContext,
        ));

        $operation->_context->{self::CONTEXT_ARGUMENT_METADATA} = $argumentMetadata;
        $operation->_context->{self::CONTEXT_MODEL_REF} = $modelRef;
    }
}
