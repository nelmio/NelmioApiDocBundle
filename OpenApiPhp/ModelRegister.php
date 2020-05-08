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

use Nelmio\ApiDocBundle\Annotation\Model as ModelAnnotation;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type;

/**
 * Resolves the path in SwaggerPhp annotation when needed.
 *
 * @internal
 */
final class ModelRegister
{
    /** @var ModelRegistry */
    private $modelRegistry;

    /** @var string */
    private $mediaType;

    public function __construct(ModelRegistry $modelRegistry, string $mediaType = 'json')
    {
        if (!in_array($mediaType, ['json', 'xml'])) {
            throw new \InvalidArgumentException('Default media type can be either json or xml.');
        }
        $this->modelRegistry = $modelRegistry;
        $this->mediaType = $mediaType;
    }

    public function __invoke(Analysis $analysis, array $parentGroups = null)
    {
        foreach ($analysis->annotations as $annotation) {
            // @Model using the ref field
            if ($annotation instanceof OA\Schema && $annotation->ref instanceof ModelAnnotation) {
                $model = $annotation->ref;

                $annotation->ref = $this->modelRegistry->register(new Model($this->createType($model->type), $this->getGroups($model, $parentGroups), $model->options));

                // It is no longer an unmerged annotation
                $this->detach($model, $annotation, $analysis);

                continue;
            }

            if (($annotation instanceof OA\Response || $annotation instanceof OA\RequestBody) && $annotation->ref instanceof ModelAnnotation) {
                $model = $annotation->ref;
                $annotation->ref = OA\UNDEFINED;
                $properties = [
                    '_context' => Util::createContext(['nested' => $annotation], $annotation->_context),
                    'ref' => $this->modelRegistry->register(new Model($this->createType($model->type), $this->getGroups($model, $parentGroups), $model->options)),
                ];

                switch ($this->mediaType) {
                    case 'json':
                        $modelAnnotation = new OA\JsonContent($properties);

                        break;
                    case 'xml':
                        $modelAnnotation = new OA\XmlContent($properties);

                        break;
                    default:
                        throw new \InvalidArgumentException(sprintf("@Model annotation is not compatible with the media type '%s'. It must be one of 'json' or 'xml'.", $this->mediaType));
                }

                $annotation->merge([$modelAnnotation]);
                $analysis->addAnnotation($modelAnnotation, null);

                $this->detach($model, $annotation, $analysis);

                continue;
            }

            // Implicit usages
            if ($annotation instanceof OA\Parameter) {
                if ($annotation->schema instanceof OA\Schema && 'array' === $annotation->schema->type) {
                    $annotationClass = OA\Items::class;
                } else {
                    $annotationClass = OA\Schema::class;
                }
            } else {
                continue;
            }

            $model = $this->getModel($annotation);
            if (null === $model) {
                continue;
            }

            if (!is_string($model->type)) {
                // Ignore invalid annotations, they are validated later
                continue;
            }

            $annotation->merge([new $annotationClass([
                'ref' => $this->modelRegistry->register(new Model($this->createType($model->type), $this->getGroups($model, $parentGroups), $model->options)),
            ])]);

            // It is no longer an unmerged annotation
            $this->detach($model, $annotation, $analysis);
        }
    }

    private function getGroups(ModelAnnotation $model, array $parentGroups = null)
    {
        if (null === $model->groups) {
            return $parentGroups;
        }

        return array_merge($parentGroups ?? [], $model->groups);
    }

    private function detach(ModelAnnotation $model, OA\AbstractAnnotation $annotation, Analysis $analysis)
    {
        foreach ($annotation->_unmerged as $key => $unmerged) {
            if ($unmerged === $model) {
                unset($annotation->_unmerged[$key]);

                break;
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
        foreach ($annotation->_unmerged as $unmerged) {
            if ($unmerged instanceof ModelAnnotation) {
                return $unmerged;
            }
        }

        return null;
    }
}
