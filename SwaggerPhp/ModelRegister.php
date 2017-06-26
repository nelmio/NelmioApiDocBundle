<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\SwaggerPhp;

use Nelmio\ApiDocBundle\Annotation\Model as ModelAnnotation;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Swagger\Analysis;
use Swagger\Annotations\Items;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Response;
use Swagger\Annotations\Schema;
use Symfony\Component\PropertyInfo\Type;

/**
 * Resolves the path in SwaggerPhp annotation when needed.
 *
 * @internal
 */
final class ModelRegister
{
    private $modelRegistry;

    public function __construct(ModelRegistry $modelRegistry)
    {
        $this->modelRegistry = $modelRegistry;
    }

    public function __invoke(Analysis $analysis)
    {
        foreach ($analysis->annotations as $annotation) {
            if ($annotation instanceof Response) {
                $annotationClass = Schema::class;
            } elseif ($annotation instanceof Parameter) {
                if ('array' === $annotation->type) {
                    $annotationClass = Items::class;
                } else {
                    $annotationClass = Schema::class;
                }
            } elseif ($annotation instanceof Schema) {
                $annotationClass = Items::class;
            } else {
                continue;
            }

            $model = null;
            foreach ($annotation->_unmerged as $unmerged) {
                if ($unmerged instanceof ModelAnnotation) {
                    $model = $unmerged;

                    break;
                }
            }

            if (null === $model || !$model instanceof ModelAnnotation) {
                continue;
            }

            if (!is_string($model->type)) {
                // Ignore invalid annotations, they are validated later
                continue;
            }

            $annotation->merge([new $annotationClass([
                'ref' => $this->modelRegistry->register(new Model($this->createType($model->type), $model->groups)),
            ])]);

            // It is no longer an unmerged annotation
            foreach ($annotation->_unmerged as $key => $unmerged) {
                if ($unmerged === $model) {
                    unset($annotation->_unmerged[$key]);

                    break;
                }
            }
            $analysis->annotations->detach($model);
        }
    }

    private function createType(string $type): Type
    {
        if ('[]' === substr($type, -2)) {
            return new Type(Type::BUILTIN_TYPE_ARRAY, false, null, true, null, $this->createType(substr($type, 0, -2)));
        }

        return new Type(Type::BUILTIN_TYPE_OBJECT, false, $type);
    }
}
