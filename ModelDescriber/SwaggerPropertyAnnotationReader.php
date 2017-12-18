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
use EXSyst\Component\Swagger\Items;
use EXSyst\Component\Swagger\Schema;
use Swagger\Annotations\Property as SwgProperty;
use Swagger\Annotations\Definition as SwgDefinition;
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

    /**
     * @param \ReflectionProperty $reflectionProperty
     * @param Items|Schema        $property
     */
    public function updateWithSwaggerPropertyAnnotation(\ReflectionProperty $reflectionProperty, $property)
    {
        $swgProperty = $this->annotationsReader->getPropertyAnnotation($reflectionProperty, SwgProperty::class);
        if ($swgProperty instanceof SwgProperty) {
            if ($swgProperty->type !== null) {
                $property->setType($swgProperty->type);
            }
            if ($swgProperty->default !== UNDEFINED) {
                $property->setDefault($swgProperty->default);
            }
            if ($swgProperty->enum !== null) {
                $property->setEnum($swgProperty->enum);
            }
            if ($property instanceof Schema) {
                if ($swgProperty->description !== null) {
                    $property->setDescription($swgProperty->description);
                }
                if ($swgProperty->title !== null) {
                    $property->setTitle($swgProperty->title);
                }
                if ($swgProperty->example !== null) {
                    $property->setExample($swgProperty->example);
                }
                if ($swgProperty->readOnly !== null) {
                    $property->setReadOnly($swgProperty->readOnly);
                }
            }
        }

        $swgDefinition = $this->annotationsReader->getClassAnnotation($reflectionProperty->getDeclaringClass(), SwgDefinition::class);
        if ($swgDefinition instanceof SwgDefinition) {
            if (in_array($reflectionProperty->getName(), $swgDefinition->required)) {
                $property->setRequired(true);
            }
        }
    }
}
