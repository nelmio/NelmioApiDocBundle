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
use Swagger\Annotations\Definition;
use Swagger\Annotations\Property;

/**
 * @internal
 */
class AnnotationsReader
{
    private $phpDocReader;
    private $swgAnnotationsReader;
    private $symfonyConstraintAnnotationReader;

    public function __construct(Reader $annotationsReader, ModelRegistry $modelRegistry)
    {
        $this->phpDocReader = new PropertyPhpDocReader();
        $this->swgAnnotationsReader = new SwgAnnotationsReader($annotationsReader, $modelRegistry);
        $this->symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader($annotationsReader);
    }

    public function updateDefinition(\ReflectionClass $reflectionClass, Definition $definition)
    {
        $this->swgAnnotationsReader->updateDefinition($reflectionClass, $definition);
        $this->symfonyConstraintAnnotationReader->setSchema($definition);
    }

    public function getPropertyName(\ReflectionProperty $reflectionProperty, string $default): string
    {
        return $this->swgAnnotationsReader->getPropertyName($reflectionProperty, $default);
    }

    public function updateProperty(\ReflectionProperty $reflectionProperty, Property $property, array $serializationGroups = null)
    {
        $this->phpDocReader->updateProperty($reflectionProperty, $property);
        $this->swgAnnotationsReader->updateProperty($reflectionProperty, $property, $serializationGroups);
        $this->symfonyConstraintAnnotationReader->updateProperty($reflectionProperty, $property);
    }
}
