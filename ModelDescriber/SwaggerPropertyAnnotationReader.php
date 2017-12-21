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
            if (null !== $swgProperty->type) {
                $property->setType($swgProperty->type);
            }
            if (UNDEFINED !== $swgProperty->default) {
                $property->setDefault($swgProperty->default);
            }
            if (null !== $swgProperty->enum) {
                $property->setEnum($swgProperty->enum);
            }
            if ($property instanceof Schema) {
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
            }
        }
    }
}
