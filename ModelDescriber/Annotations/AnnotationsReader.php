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
use OpenApi\Generator;

/**
 * @internal
 */
class AnnotationsReader
{
    private $phpDocReader;
    private $openApiAnnotationsReader;
    private $symfonyConstraintAnnotationReader;

    public function __construct(
        ?Reader $annotationsReader,
        ModelRegistry $modelRegistry,
        array $mediaTypes,
        bool $useValidationGroups = false
    ) {
        $this->phpDocReader = new PropertyPhpDocReader();
        $this->openApiAnnotationsReader = new OpenApiAnnotationsReader($annotationsReader, $modelRegistry, $mediaTypes);
        $this->symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader(
            $annotationsReader,
            $useValidationGroups
        );
    }

    public function updateDefinition(\ReflectionClass $reflectionClass, OA\Schema $schema): UpdateClassDefinitionResult
    {
        $this->openApiAnnotationsReader->updateSchema($reflectionClass, $schema);
        $this->symfonyConstraintAnnotationReader->setSchema($schema);

        return new UpdateClassDefinitionResult(
            $this->shouldDescribeModelProperties($schema)
        );
    }

    public function getPropertyName($reflection, string $default): string
    {
        return $this->openApiAnnotationsReader->getPropertyName($reflection, $default);
    }

    public function updateProperty($reflection, OA\Property $property, array $serializationGroups = null): void
    {
        $this->openApiAnnotationsReader->updateProperty($reflection, $property, $serializationGroups);
        $this->phpDocReader->updateProperty($reflection, $property);
        $this->symfonyConstraintAnnotationReader->updateProperty($reflection, $property, $serializationGroups);

        // Make sure that a possibly set default value for a property is used, when not overwritten by an annotation
        // or attribute.
        if (Generator::UNDEFINED !== $property->default) {
            return;
        }

        if (!$reflection instanceof \ReflectionProperty) {
            return;
        }

        if (!$reflection->hasDefaultValue()) {
            return;
        }

        $default = $reflection->getDefaultValue();
        if (null === $default) {
            return;
        }

        $property->default = $reflection->getDefaultValue();
    }

    /**
     * if an objects schema type and ref are undefined OR the object was manually
     * defined as an object, then we're good to do the normal describe flow of
     * class properties.
     */
    private function shouldDescribeModelProperties(OA\Schema $schema): bool
    {
        return (Generator::UNDEFINED === $schema->type || 'object' === $schema->type)
            && Generator::UNDEFINED === $schema->ref;
    }
}
