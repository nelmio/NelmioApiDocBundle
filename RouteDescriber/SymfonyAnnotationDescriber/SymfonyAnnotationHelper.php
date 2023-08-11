<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber\SymfonyAnnotationDescriber;

use OpenApi\Annotations as OA;
use OpenApi\Generator;
use ReflectionParameter;

final class SymfonyAnnotationHelper
{
    /**
     * @param class-string<T> $attribute
     *
     * @return T|null
     *
     * @template T of object
     */
    public static function getAttribute(ReflectionParameter $parameter, string $attribute): ?object
    {
        if ($attribute = $parameter->getAttributes($attribute, \ReflectionAttribute::IS_INSTANCEOF)) {
            return $attribute[0]->newInstance();
        }

        return null;
    }

    public static function describeCommonSchemaFromParameter(OA\Schema $schema, ReflectionParameter $parameter): void
    {
        if ($parameter->isDefaultValueAvailable()) {
            $schema->default = $parameter->getDefaultValue();
        }

        if (Generator::UNDEFINED === $schema->type) {
            if ($parameter->getType()->isBuiltin()) {
                $schema->type = $parameter->getType()->getName();
            }
        }
    }
}
