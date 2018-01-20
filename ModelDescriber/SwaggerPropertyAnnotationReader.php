<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\ModelDescriber;

use Doctrine\Common\Annotations\Reader;
use EXSyst\Component\Swagger\Schema;
use Swagger\Annotations\Property as SwgProperty;
use const Swagger\Annotations\UNDEFINED;

/**
 * @internal
 */
class SwaggerPropertyAnnotationReader
{
    private $annotationsReader;

    public function __construct(Reader $annotationsReader)
    {
        $this->annotationsReader = $annotationsReader;
    }

    public function updateWithSwaggerPropertyAnnotation(\ReflectionProperty $reflectionProperty, Schema $property)
    {
        /** @var SwgProperty $swgProperty */
        if (!$swgProperty = $this->annotationsReader->getPropertyAnnotation($reflectionProperty, SwgProperty::class)) {
            return;
        }

        if (null !== $swgProperty->type) {
            $property->setType($swgProperty->type);
        }
        if (UNDEFINED !== $swgProperty->default) {
            $property->setDefault($swgProperty->default);
        }
        if (null !== $swgProperty->enum) {
            $property->setEnum($swgProperty->enum);
        }
        if (null !== $swgProperty->description) {
            $property->setDescription($swgProperty->description);
        }
        if (null !== $swgProperty->title) {
            $property->setTitle($swgProperty->title);
        }
        if (null !== $swgProperty->example) {
            $property->setExample($swgProperty->example);
        }
        if (null !== $swgProperty->readOnly) {
            $property->setReadOnly($swgProperty->readOnly);
        }
        if (null !== $swgProperty->minimum) {
            $property->setMinimum($swgProperty->minimum);
        }
        if (null !== $swgProperty->exclusiveMinimum) {
            $property->setExclusiveMinimum($swgProperty->exclusiveMinimum);
        }
        if (null !== $swgProperty->maximum) {
            $property->setMaximum($swgProperty->maximum);
        }
        if (null !== $swgProperty->exclusiveMaximum) {
            $property->setExclusiveMaximum($swgProperty->exclusiveMaximum);
        }
        if (null !== $swgProperty->minLength) {
            $property->setMinLength($swgProperty->minLength);
        }
        if (null !== $swgProperty->maxLength) {
            $property->setMaxLength($swgProperty->maxLength);
        }
        if (null !== $swgProperty->minItems) {
            $property->setMinItems($swgProperty->minItems);
        }
        if (null !== $swgProperty->maxItems) {
            $property->setMaxItems($swgProperty->maxItems);
        }
        if (null !== $swgProperty->uniqueItems) {
            $property->setUniqueItems($swgProperty->uniqueItems);
        }
        if (null !== $swgProperty->multipleOf) {
            $property->setMultipleOf($swgProperty->multipleOf);
        }
        if (null !== $swgProperty->pattern) {
            $property->setPattern($swgProperty->pattern);
        }
    }
}
