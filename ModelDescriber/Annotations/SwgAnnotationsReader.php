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
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\SwaggerPhp\ModelRegister;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;

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

    public function updateDefinition(\ReflectionClass $reflectionClass, OA\Schema $schema)
    {
        /** @var Definition $classDefinition */
        if (!$classDefinition = $this->annotationsReader->getClassAnnotation($reflectionClass, OA\Schema::class)) {
            return;
        }

        // Read @Model annotations
        $this->modelRegister->__invoke(new Analysis([$classDefinition]));

        if (!$classDefinition->validate()) {
            return;
        }

        $schema->mergeProperties($classDefinition);
    }

    public function getPropertyName(\ReflectionProperty $reflectionProperty, string $default): string
    {
        /** @var OA\Property $swgProperty */
        if (!$swgProperty = $this->annotationsReader->getPropertyAnnotation($reflectionProperty, OA\Property::class)) {
            return $default;
        }

        return $swgProperty->property ?? $default;
    }

    public function updateProperty(\ReflectionProperty $reflectionProperty, OA\Property $property, array $serializationGroups = null)
    {
        if (!$swgProperty = $this->annotationsReader->getPropertyAnnotation($reflectionProperty, OA\Property::class)) {
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

        $property->mergeProperties($swgProperty);
    }
}
