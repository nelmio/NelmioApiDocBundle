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

/**
 * @internal
 */
class AnnotationsReader
{
    private $annotationsReader;

    private $phpDocReader;
    private $swgAnnotationsReader;
    private $symfonyConstraintAnnotationReader;

    public function __construct(Reader $annotationsReader)
    {
        $this->annotationsReader = $annotationsReader;

        $this->phpDocReader = new PropertyPhpDocReader();
        $this->swgAnnotationsReader = new SwgAnnotationsReader($annotationsReader);
        $this->symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader($annotationsReader);
    }

    public function updateDefinition(\ReflectionClass $reflectionClass, Schema $schema)
    {
        $this->swgAnnotationsReader->updateDefinition($reflectionClass, $schema);
        $this->symfonyConstraintAnnotationReader->setSchema($schema);
    }

    public function updateProperty(\ReflectionProperty $reflectionProperty, Schema $property)
    {
        $this->phpDocReader->updateProperty($reflectionProperty, $property);
        $this->swgAnnotationsReader->updateProperty($reflectionProperty, $property);
        $this->symfonyConstraintAnnotationReader->updateProperty($reflectionProperty, $property);
    }
}
