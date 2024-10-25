<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\OpenApiPhp;

use Nelmio\ApiDocBundle\Attribute\Model as ModelAnnotation;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\PropertyInfo\Type;

/**
 * Resolves the path in SwaggerPhp annotation when needed.
 *
 * @internal
 */
final class ModelRegister
{
    private ModelRegistry $modelRegistry;

    /** @var string[] */
    private array $mediaTypes;

    /**
     * @param string[] $mediaTypes
     */
    public function __construct(ModelRegistry $modelRegistry, array $mediaTypes)
    {
        $this->modelRegistry = $modelRegistry;
        $this->mediaTypes = $mediaTypes;
    }

    /**
     * @param string[]|null $parentGroups
     */
    public function __invoke(Analysis $analysis, ?array $parentGroups = null): void
    {
        foreach ($analysis->annotations as $annotation) {
            // @Model using the ref field
            if ($annotation instanceof OA\Schema && $annotation->ref instanceof ModelAnnotation) {
                $model = $annotation->ref;

                $annotation->ref = $this->modelRegistry->register(new Model($this->createType($model->type), $this->getGroups($model, $parentGroups), $model->options, $model->serializationContext));

                // It is no longer an unmerged annotation
                $this->detach($model, $annotation, $analysis);

                continue;
            }

            // Misusage of ::$ref
            if (($annotation instanceof OA\Response || $annotation instanceof OA\RequestBody) && $annotation->ref instanceof ModelAnnotation) {
                throw new \InvalidArgumentException(sprintf('Using @Model inside @%s::$ref is not allowed. You should use ::$ref with @Property, @Parameter, @Schema, @Items but within @Response or @RequestBody you should put @Model directly at the root of the annotation : `@Response(..., @Model(...))`.', get_class($annotation)));
            }

            // Implicit usages

            // We don't use $ref for @Responses, @RequestBody and @Parameter to respect semantics
            // We don't replace these objects with the @Model found (we inject it in a subfield) whereas we do for @Schemas

            $model = $this->getModel($annotation); // We check whether there is a @Model annotation nested
            if (null === $model) {
                continue;
            }

            if ($annotation instanceof OA\Response || $annotation instanceof OA\RequestBody) {
                $properties = [
                    '_context' => Util::createContext(['nested' => $annotation], $annotation->_context),
                    'ref' => $this->modelRegistry->register(new Model($this->createType($model->type), $this->getGroups($model, $parentGroups), $model->options, $model->serializationContext)),
                ];

                foreach ($this->mediaTypes as $mediaType) {
                    $this->createContentForMediaType($mediaType, $properties, $annotation, $analysis);
                }
                $this->detach($model, $annotation, $analysis);

                continue;
            }

            if (!$annotation instanceof OA\Parameter) {
                throw new \InvalidArgumentException(sprintf("@Model annotation can't be nested with an annotation of type @%s.", get_class($annotation)));
            }

            if ($annotation->schema instanceof OA\Schema && 'array' === $annotation->schema->type) {
                $annotationClass = OA\Items::class;
            } else {
                $annotationClass = OA\Schema::class;
            }

            $annotation->merge([new $annotationClass([
                'ref' => $this->modelRegistry->register(new Model($this->createType($model->type), $this->getGroups($model, $parentGroups), $model->options, $model->serializationContext)),
            ])]);

            // It is no longer an unmerged annotation
            $this->detach($model, $annotation, $analysis);
        }
    }

    /**
     * @param string[]|null $parentGroups
     *
     * @return string[]|null
     */
    private function getGroups(ModelAnnotation $model, ?array $parentGroups = null): ?array
    {
        if (null === $model->groups) {
            return $parentGroups;
        }

        return array_merge($parentGroups ?? [], $model->groups);
    }

    private function detach(ModelAnnotation $model, OA\AbstractAnnotation $annotation, Analysis $analysis): void
    {
        if (Generator::UNDEFINED !== $annotation->attachables) {
            foreach ($annotation->attachables as $key => $attachable) {
                if ($attachable === $model) {
                    unset($annotation->attachables[$key]);

                    break;
                }
            }
        }

        $analysis->annotations->detach($model);
    }

    private function createType(string $type): Type
    {
        if ('[]' === substr($type, -2)) {
            return new Type(Type::BUILTIN_TYPE_ARRAY, false, null, true, null, $this->createType(substr($type, 0, -2)));
        }

        return new Type(Type::BUILTIN_TYPE_OBJECT, false, $type);
    }

    private function getModel(OA\AbstractAnnotation $annotation): ?ModelAnnotation
    {
        if (Generator::UNDEFINED !== $annotation->attachables) {
            foreach ($annotation->attachables as $attachable) {
                if ($attachable instanceof ModelAnnotation) {
                    return $attachable;
                }
            }
        }

        return null;
    }

    /**
     * @param mixed[] $properties
     */
    private function createContentForMediaType(
        string $type,
        array $properties,
        OA\AbstractAnnotation $annotation,
        Analysis $analysis
    ): void {
        switch ($type) {
            case 'json':
                $modelAnnotation = new OA\JsonContent($properties);

                break;
            case 'xml':
                $modelAnnotation = new OA\XmlContent($properties);

                break;
            default:
                throw new \InvalidArgumentException(sprintf("@Model annotation is not compatible with the media types '%s'. It must be one of 'json' or 'xml'.", implode(',', $this->mediaTypes)));
        }

        $annotation->merge([$modelAnnotation]);
        $analysis->addAnnotation($modelAnnotation, $properties['_context']);
    }
}
