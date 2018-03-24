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
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\SwaggerPhp\ModelRegister;
use Swagger\Analysis;
use Swagger\Annotations\Definition as SwgDefinition;
use Swagger\Annotations\Property as SwgProperty;
use Swagger\Context;

/**
 * @internal
 */
class SwgAnnotationsReader
{
    private $annotationsReader;
    private $modelRegister;

    public function __construct(Reader $annotationsReader, ModelRegistry $modelRegistry)
    {
        $this->annotationsReader = $annotationsReader;
        $this->modelRegister = new ModelRegister($modelRegistry);
    }

    public function updateDefinition(\ReflectionClass $reflectionClass, Schema $schema)
    {
        /** @var SwgDefinition $swgDefinition */
        if (!$swgDefinition = $this->annotationsReader->getClassAnnotation($reflectionClass, SwgDefinition::class)) {
            return;
        }

        // Read @Model annotations
        $this->modelRegister->__invoke(new Analysis([$swgDefinition]));

        if (!$swgDefinition->validate()) {
            return;
        }

        $schema->merge(json_decode(json_encode($swgDefinition)));
    }

    public function getPropertyName(\ReflectionProperty $reflectionProperty, string $default): string
    {
        /** @var SwgProperty $swgProperty */
        if (!$swgProperty = $this->annotationsReader->getPropertyAnnotation($reflectionProperty, SwgProperty::class)) {
            return $default;
        }

        return $swgProperty->property ?? $default;
    }

    public function updateProperty(\ReflectionProperty $reflectionProperty, Schema $property, array $serializationGroups = null)
    {
        if (!$swgProperty = $this->annotationsReader->getPropertyAnnotation($reflectionProperty, SwgProperty::class)) {
            return;
        }

        $declaringClass = $reflectionProperty->getDeclaringClass();
        $context = new Context([
            'namespace' => $declaringClass->getNamespaceName(),
            'class' => $declaringClass->getShortName(),
            'property' => $reflectionProperty->name,
            'filename' => $declaringClass->getFileName(),
        ]);
        $swgProperty->_context = $context;

        // Read @Model annotations
        $this->modelRegister->__invoke(new Analysis([$swgProperty]), $serializationGroups);

        if (!$swgProperty->validate()) {
            return;
        }

        $property->merge(json_decode(json_encode($swgProperty)));
    }
}
