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
use OpenApi\Annotations as OA;
use ReflectionClass;
use ReflectionProperty;

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

    public function updateSchema(ReflectionClass $reflectionClass, OA\Schema $schema): void
    {
        $this->swgAnnotationsReader->updateSchema($reflectionClass, $schema);
        $this->symfonyConstraintAnnotationReader->setSchema($schema);
    }

    public function getPropertyName(ReflectionProperty $reflectionProperty, string $default): string
    {
        return $this->swgAnnotationsReader->getPropertyName($reflectionProperty, $default);
    }

    public function updateProperty(ReflectionProperty $reflectionProperty, OA\Property $property, array $serializationGroups = null): void
    {
        $this->phpDocReader->updateProperty($reflectionProperty, $property);
        $this->swgAnnotationsReader->updateProperty($reflectionProperty, $property, $serializationGroups);
        $this->symfonyConstraintAnnotationReader->updateProperty($reflectionProperty, $property);
    }
}
