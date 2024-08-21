<?php

declare(strict_types=1);

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Validator\Constraints\GroupSequence;

final class SymfonyMapRequestPayloadDescriber implements RouteArgumentDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    public const CONTEXT_ARGUMENT_METADATA = 'nelmio_api_doc_bundle.argument_metadata.'.self::class;
    public const CONTEXT_MODEL_REF = 'nelmio_api_doc_bundle.model_ref.'.self::class;

    public function describe(ArgumentMetadata $argumentMetadata, OA\Operation $operation): void
    {
        if (!$attribute = $argumentMetadata->getAttributes(MapRequestPayload::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null) {
            return;
        }

        $typeClass = $argumentMetadata->getType();

        $reflectionAttribute = new \ReflectionClass(MapRequestPayload::class);
        if (Type::BUILTIN_TYPE_ARRAY === $typeClass && $reflectionAttribute->hasProperty('type') && null !== $attribute->type) {
            $typeClass = $attribute->type;
        }

        $modelRef = $this->modelRegistry->register(new Model(
            new Type(Type::BUILTIN_TYPE_OBJECT, false, $typeClass),
            groups: $this->getGroups($attribute),
            serializationContext: $attribute->serializationContext,
        ));

        $operation->_context->{self::CONTEXT_ARGUMENT_METADATA} = $argumentMetadata;
        $operation->_context->{self::CONTEXT_MODEL_REF} = $modelRef;
    }

    /**
     * @return string[]|null
     */
    private function getGroups(MapRequestPayload $attribute): ?array
    {
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
