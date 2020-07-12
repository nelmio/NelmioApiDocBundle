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
use Nelmio\ApiDocBundle\Model\ModelRegistry;

/**
 * @internal
 */
class AnnotationsReader
{
    private $annotationsReader;
    private $modelRegistry;

    private $phpDocReader;
    private $swgAnnotationsReader;
    private $symfonyConstraintAnnotationReader;

    public function __construct(Reader $annotationsReader, ModelRegistry $modelRegistry)
    {
        $this->annotationsReader = $annotationsReader;
        $this->modelRegistry = $modelRegistry;

        $this->phpDocReader = new PropertyPhpDocReader();
        $this->swgAnnotationsReader = new SwgAnnotationsReader($annotationsReader, $modelRegistry);
        $this->symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader($annotationsReader);
    }

    public function updateDefinition(\ReflectionClass $reflectionClass, Schema $schema)
    {
        $this->swgAnnotationsReader->updateDefinition($reflectionClass, $schema);
        $this->symfonyConstraintAnnotationReader->setSchema($schema);
    }

    public function getPropertyName($reflection, string $default): string
    {
        return $this->swgAnnotationsReader->getPropertyName($reflection, $default);
    }

    public function updateProperty($reflection, Schema $property, array $serializationGroups = null)
    {
        $this->phpDocReader->updateProperty($reflection, $property);
        $this->swgAnnotationsReader->updateProperty($reflection, $property, $serializationGroups);
        $this->symfonyConstraintAnnotationReader->updateProperty($reflection, $property);
    }
}
