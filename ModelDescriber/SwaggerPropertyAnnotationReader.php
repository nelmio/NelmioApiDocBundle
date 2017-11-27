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

use EXSyst\Component\Swagger\Schema;
use EXSyst\Component\Swagger\Items;
use Swagger\Annotations\Property as SwgProperty;
use Doctrine\Common\Annotations\Reader;

/**
 * @internal
 */
class SwaggerPropertyAnnotationReader
{
    /**
     * @var Reader
     */
    private $annotationsReader;

    /**
     * @param Reader $annotationsReader
     */
    public function __construct(Reader $annotationsReader)
    {
        $this->annotationsReader = $annotationsReader;
    }

    /**
     * @param \ReflectionProperty $reflectionProperty
     * @param Items|Schema        $property
     * @return Items|Schema
     */
    public function readSwaggerPropertyAnnotation(\ReflectionProperty $reflectionProperty, $property)
    {
        $swgProperty = $this->annotationsReader->getPropertyAnnotation($reflectionProperty, SwgProperty::class);
        if ($swgProperty instanceof SwgProperty) {
            if ($swgProperty->description !== null) {
                $property->setDescription($swgProperty->description);
            }
            if ($swgProperty->type !== null) {
                $property->setType($swgProperty->type);
            }
            if ($swgProperty->readOnly !== null) {
                $property->setReadOnly($swgProperty->readOnly);
            }
            if ($swgProperty->title !== null) {
                $property->setTitle($swgProperty->title);
            }
            if ($swgProperty->example !== null) {
                $property->setExample((string) $swgProperty->example);
            }
        }

        return $property;
    }
}
