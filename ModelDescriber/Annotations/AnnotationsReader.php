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

/**
 * @internal
 */
class AnnotationsReader
{
    private $annotationsReader;
    private $modelRegistry;

    private $phpDocReader;
    private $openApiAnnotationsReader;
    private $symfonyConstraintAnnotationReader;

    public function __construct(Reader $annotationsReader, ModelRegistry $modelRegistry, array $mediaTypes)
    {
        $this->annotationsReader = $annotationsReader;
        $this->modelRegistry = $modelRegistry;

        $this->phpDocReader = new PropertyPhpDocReader();
        $this->openApiAnnotationsReader = new OpenApiAnnotationsReader($annotationsReader, $modelRegistry, $mediaTypes);
        $this->symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader($annotationsReader);
    }

    public function updateDefinition(\ReflectionClass $reflectionClass, OA\Schema $schema): void
    {
        $this->openApiAnnotationsReader->updateSchema($reflectionClass, $schema);
        $this->symfonyConstraintAnnotationReader->setSchema($schema);
    }

    public function getPropertyName($reflection, string $default): string
    {
        return $this->openApiAnnotationsReader->getPropertyName($reflection, $default);
    }

    public function updateProperty($reflection, OA\Property $property, array $serializationGroups = null): void
    {
        $this->openApiAnnotationsReader->updateProperty($reflection, $property, $serializationGroups);
        $this->phpDocReader->updateProperty($reflection, $property);
        $this->symfonyConstraintAnnotationReader->updateProperty($reflection, $property);
    }
}
