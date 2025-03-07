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

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class SymfonyMapUploadedFileDescriber implements RouteArgumentDescriberInterface
{
    public const CONTEXT_ARGUMENT_METADATA = 'nelmio_api_doc_bundle.argument_metadata.'.self::class;
    public const CONTEXT_MODEL_REF = 'nelmio_api_doc_bundle.model_ref.'.self::class;

    public function describe(ArgumentMetadata $argumentMetadata, OA\Operation $operation): void
    {
        if (!$attribute = $argumentMetadata->getAttributes(MapUploadedFile::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null) {
            return;
        }

        $name = $attribute->name ?? $argumentMetadata->getName();
        $body = Util::getChild($operation, OA\RequestBody::class);

        $mediaType = Util::getCollectionItem($body, OA\MediaType::class, [
            'mediaType' => 'multipart/form-data',
        ]);

        /** @var OA\Schema $schema */
        $schema = Util::getChild($mediaType, OA\Schema::class, [
            'type' => 'object',
        ]);

        $property = Util::getCollectionItem($schema, OA\Property::class, ['property' => $name]);
        Util::modifyAnnotationValue($property, 'type', 'string');
        Util::modifyAnnotationValue($property, 'format', 'binary');
    }
}
