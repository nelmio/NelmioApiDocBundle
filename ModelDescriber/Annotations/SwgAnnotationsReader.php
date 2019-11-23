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
use ReflectionClass;
use ReflectionProperty;
use const OpenApi\UNDEFINED;

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

    public function updateSchema(ReflectionClass $reflectionClass, OA\Schema $schema): void
    {
        /** @var OA\Schema $classSchema */
        if (!$classSchema = $this->annotationsReader->getClassAnnotation($reflectionClass, OA\Schema::class)) {
            return;
        }

        // Read @Model annotations
        $this->modelRegister->__invoke(new Analysis([$classSchema]));

        if (!$classSchema->validate()) {
            return;
        }

        $schema->mergeProperties($classSchema);
    }

    public function getPropertyName(ReflectionProperty $reflectionProperty, string $default): string
    {
        /** @var OA\Property $swgProperty */
        if (!$swgProperty = $this->annotationsReader->getPropertyAnnotation($reflectionProperty, OA\Property::class)) {
            return $default;
        }

        return isset($swgProperty->property) && UNDEFINED !== $swgProperty->property ?
            $swgProperty->property :
            $default;
    }

    public function updateProperty(ReflectionProperty $reflectionProperty, OA\Property $property, array $serializationGroups = null): void
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
