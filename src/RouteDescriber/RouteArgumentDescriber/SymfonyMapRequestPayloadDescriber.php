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
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\PropertyDescriber\PropertyDescriberInterface;
use Nelmio\ApiDocBundle\TypeDescriber\TypeDescriberInterface;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\TypeInfo\Type;

final class SymfonyMapRequestPayloadDescriber implements RouteArgumentDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    public function __construct(
        private PropertyDescriberInterface|TypeDescriberInterface $propertyDescriber,
    ) {
    }

    /** @deprecated */
    public const CONTEXT_ARGUMENT_METADATA = 'nelmio_api_doc_bundle.argument_metadata.'.self::class;
    /** @deprecated */
    public const CONTEXT_MODEL_REF = 'nelmio_api_doc_bundle.model_ref.'.self::class;

    public function describe(ArgumentMetadata $argumentMetadata, OA\Operation $operation): void
    {
        if (!$attribute = $argumentMetadata->getAttributes(MapRequestPayload::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null) {
            return;
        }

        if (LegacyType::BUILTIN_TYPE_ARRAY === $argumentMetadata->getType() && property_exists($attribute, 'type')) {
            $type = new LegacyType(
                $argumentMetadata->getType(),
                $argumentMetadata->isNullable(),
                null,
                true,
                [new LegacyType(LegacyType::BUILTIN_TYPE_INT)],
                [new LegacyType(
                    class_exists($attribute->type) ? LegacyType::BUILTIN_TYPE_OBJECT : $attribute->type,
                    false,
                    class_exists($attribute->type) ? $attribute->type : null,
                )]
            );
        } else {
            $type = new LegacyType(
                LegacyType::BUILTIN_TYPE_OBJECT,
                $argumentMetadata->isNullable(),
                $argumentMetadata->getType(),
            );
        }

        if ($this->propertyDescriber instanceof ModelRegistryAwareInterface) {
            $this->propertyDescriber->setModelRegistry($this->modelRegistry);
        }

        /** @var OA\RequestBody $requestBody */
        $requestBody = Util::getChild($operation, OA\RequestBody::class);
        Util::modifyAnnotationValue($requestBody, 'required', !($argumentMetadata->hasDefaultValue() || $argumentMetadata->isNullable()));

        $formats = $attribute->acceptFormat;
        if (!\is_array($formats)) {
            $formats = [$attribute->acceptFormat ?? 'json'];
        }

        foreach ($formats as $format) {
            if (!Generator::isDefault($requestBody->content)) {
                continue;
            }

            $contentSchema = $this->getContentSchemaForType($requestBody, $format);

            if ($this->propertyDescriber instanceof TypeDescriberInterface) {
                $types = self::toTypeInfoType($type);
            } else {
                $types = [$type];
            }

            if ($this->propertyDescriber->supports($types, $attribute->serializationContext)) {
                $this->propertyDescriber->describe($types, $contentSchema, $attribute->serializationContext);

                return;
            }
        }
    }

    private function getContentSchemaForType(OA\RequestBody $requestBody, string $type): OA\Schema
    {
        Util::modifyAnnotationValue($requestBody, 'content', []);
        $contentType = match ($type) {
            'json' => 'application/json',
            'xml' => 'application/xml',
            default => throw new \InvalidArgumentException('Unsupported media type'),
        };

        $mediaType = Util::getCollectionItem($requestBody, OA\MediaType::class, [
            'mediaType' => $contentType,
        ]);

        return Util::getChild(
            $mediaType,
            OA\Schema::class
        );
    }

    /** @see \Symfony\Component\PropertyInfo\Util\LegacyTypeConverter::toTypeInfoType() */
    private static function toTypeInfoType(LegacyType $legacyType): Type
    {
        $typeInfoType = match ($legacyType->getBuiltinType()) {
            LegacyType::BUILTIN_TYPE_BOOL => Type::bool(),
            LegacyType::BUILTIN_TYPE_FALSE => Type::false(),
            LegacyType::BUILTIN_TYPE_TRUE => Type::true(),
            LegacyType::BUILTIN_TYPE_FLOAT => Type::float(),
            LegacyType::BUILTIN_TYPE_INT => Type::int(),
            LegacyType::BUILTIN_TYPE_STRING => Type::string(),
            LegacyType::BUILTIN_TYPE_NULL => Type::null(),
            LegacyType::BUILTIN_TYPE_ARRAY => Type::array(self::toTypeInfoType($legacyType->getCollectionValueTypes()), self::toTypeInfoType($legacyType->getCollectionKeyTypes())),
            LegacyType::BUILTIN_TYPE_OBJECT => Type::object($legacyType->getClassName()),
            default => throw new \InvalidArgumentException(\sprintf('"%s" is not a valid supported MapRequestPayload type.', $legacyType->getBuiltinType())),
        };

        return LegacyType::BUILTIN_TYPE_NULL === $legacyType->getBuiltinType() || $legacyType->isNullable()
            ? Type::nullable($typeInfoType)
            : $typeInfoType;
    }
}
