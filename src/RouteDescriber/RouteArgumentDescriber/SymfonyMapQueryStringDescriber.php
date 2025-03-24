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
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Validator\Constraints\GroupSequence;

final class SymfonyMapQueryStringDescriber implements RouteArgumentDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    public const CONTEXT_KEY = 'nelmio_api_doc_bundle.map_query_string.'.self::class;
    public const CONTEXT_ARGUMENT_METADATA = 'nelmio_api_doc_bundle.argument_metadata.'.self::class;
    public const CONTEXT_MODEL_REF = 'nelmio_api_doc_bundle.model_ref.'.self::class;

    public function describe(ArgumentMetadata $argumentMetadata, OA\Operation $operation): void
    {
        if (!$attribute = $argumentMetadata->getAttributes(MapQueryString::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null) {
            return;
        }

        $modelRef = $this->modelRegistry->register(new Model(
            new Type(Type::BUILTIN_TYPE_OBJECT, $argumentMetadata->isNullable(), $argumentMetadata->getType()),
            groups: $this->getGroups($attribute),
            serializationContext: $attribute->serializationContext,
        ));

        if (!isset($operation->_context->{self::CONTEXT_KEY})) {
            $operation->_context->{self::CONTEXT_KEY} = [];
        }

        $data = [
            self::CONTEXT_ARGUMENT_METADATA => $argumentMetadata,
            self::CONTEXT_MODEL_REF => $modelRef,
        ];

        $operation->_context->{self::CONTEXT_KEY}[] = $data;
    }

    /**
     * @return string[]|null
     */
    private function getGroups(MapQueryString $attribute): ?array
    {
        if (\is_string($attribute->validationGroups)) {
            return [$attribute->validationGroups];
        }

        if (\is_array($attribute->validationGroups)) {
            return $attribute->validationGroups;
        }

        if ($attribute->validationGroups instanceof GroupSequence) {
            return $attribute->validationGroups->groups;
        }

        return null;
    }
}
