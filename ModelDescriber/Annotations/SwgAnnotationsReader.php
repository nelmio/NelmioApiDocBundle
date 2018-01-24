<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\ModelDescriber\Annotations;

use Doctrine\Common\Annotations\Reader;
use EXSyst\Component\Swagger\Schema;
use Swagger\Annotations\Definition as SwgDefinition;
use Swagger\Annotations\Property as SwgProperty;

/**
 * @internal
 */
class SwgAnnotationsReader
{
    private $annotationsReader;

    public function __construct(Reader $annotationsReader)
    {
        $this->annotationsReader = $annotationsReader;
    }

    public function updateDefinition(\ReflectionClass $reflectionClass, Schema $schema)
    {
        /** @var SwgDefinition $swgDefinition */
        if (!$swgDefinition = $this->annotationsReader->getClassAnnotation($reflectionClass, SwgDefinition::class)) {
            return;
        }

        if (null !== $swgDefinition->required) {
            $schema->setRequired($swgDefinition->required);
        }
    }

    public function updateProperty(\ReflectionProperty $reflectionProperty, Schema $property)
    {
        /** @var SwgProperty $swgProperty */
        if (!$swgProperty = $this->annotationsReader->getPropertyAnnotation($reflectionProperty, SwgProperty::class)) {
            return;
        }

        if (!$swgProperty->validate()) {
            return;
        }

        $property->merge(json_decode(json_encode($swgProperty)));
    }
}
