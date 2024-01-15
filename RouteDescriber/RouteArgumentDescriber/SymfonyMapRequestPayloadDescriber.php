<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\PropertyInfo\Type;

final class SymfonyMapRequestPayloadDescriber implements RouteArgumentDescriberInterface, ModelRegistryAwareInterface
{
    public const CONTEXT_ARGUMENT_METADATA = 'nelmio_api_doc_bundle.argument_metadata.'.self::class;
    public const CONTEXT_MODEL_REF = 'nelmio_api_doc_bundle.model_ref.'.self::class;

    use ModelRegistryAwareTrait;

    public function describe(ArgumentMetadata $argumentMetadata, OA\Operation $operation): void
    {
        /** @var MapRequestPayload $attribute */
        if (!$attribute = $argumentMetadata->getAttributes(MapRequestPayload::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null) {
            return;
        }

        $modelRef = $this->modelRegistry->register(new Model(
            new Type(Type::BUILTIN_TYPE_OBJECT, false, $argumentMetadata->getType()),
            serializationContext: $attribute->serializationContext,
        ));

        $operation->_context->{self::CONTEXT_ARGUMENT_METADATA} = $argumentMetadata;
        $operation->_context->{self::CONTEXT_MODEL_REF} = $modelRef;
    }
}
