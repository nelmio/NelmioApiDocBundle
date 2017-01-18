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
            if (!$annotation instanceof ModelAnnotation || $annotation->_context->not('nested')) {
                continue;
            }

            if (!is_string($annotation->type)) {
                // Ignore invalid annotations, they are validated later
                continue;
            }

            $parent = $annotation->_context->nested;
            if (!$parent instanceof Response && !$parent instanceof Parameter && !$parent instanceof Schema) {
                continue;
            }

            $annotationClass = Schema::class;
            if ($parent instanceof Schema) {
                $annotationClass = Items::class;
            }

            $parent->merge([new $annotationClass([
                'ref' => $this->modelRegistry->register(new Model($this->createType($annotation->type))),
            ])]);

            // It is no longer an unmerged annotation
            foreach ($parent->_unmerged as $key => $unmerged) {
                if ($unmerged === $annotation) {
                    unset($parent->_unmerged[$key]);

                    break;
                }
            }
            $analysis->annotations->detach($annotation);
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
