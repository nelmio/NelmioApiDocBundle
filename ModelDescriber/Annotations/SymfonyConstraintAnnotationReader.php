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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @internal
 */
class SymfonyConstraintAnnotationReader
{
    /**
     * @var Reader
     */
    private $annotationsReader;

    /**
     * @var Schema
     */
    private $schema;

    public function __construct(Reader $annotationsReader)
    {
        $this->annotationsReader = $annotationsReader;
    }

    /**
     * Update the given property and schema with defined Symfony constraints.
     */
    public function updateProperty(\ReflectionProperty $reflectionProperty, Schema $property)
    {
        $annotations = $this->annotationsReader->getPropertyAnnotations($reflectionProperty);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof Assert\NotBlank || $annotation instanceof Assert\NotNull) {
                $this->updateSchemaDefinitionWithRequiredProperty($reflectionProperty);
            }

            if ($annotation instanceof Assert\Length) {
                if ($annotation->min > 0) {
                    $this->updateSchemaDefinitionWithRequiredProperty($reflectionProperty);
                }

                $property->setMinLength($annotation->min);
                $property->setMaxLength($annotation->max);
            }

            if ($annotation instanceof Assert\Regex) {
                $this->appendPattern($property, $annotation->getHtmlPattern());
            }

            if ($annotation instanceof Assert\DateTime) {
                $this->appendPattern($property, $annotation->format);
            }

            if ($annotation instanceof Assert\Count) {
                $property->setMinItems($annotation->min);
                $property->setMaxItems($annotation->max);
            }

            if ($annotation instanceof Assert\Choice) {
                $property->setEnum($annotation->choices);
            }

            if ($annotation instanceof Assert\Expression) {
                $this->appendPattern($property, $annotation->message);
            }
        }
    }

    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * Set the required properties on the scheme.
     */
    private function updateSchemaDefinitionWithRequiredProperty(\ReflectionProperty $reflectionProperty)
    {
        if (null === $this->schema) {
            return;
        }

        $existingRequiredFields = $this->schema->getRequired() ?? [];

        $existingRequiredFields[] = $reflectionProperty->getName();

        $this->schema->setRequired(array_unique($existingRequiredFields));
    }

    /**
     * Append the pattern from the constraint to the existing pattern.
     */
    private function appendPattern(Schema $property, $newPattern)
    {
        if (null === $newPattern) {
            return;
        }

        if (null !== $property->getPattern()) {
            $property->setPattern(sprintf('%s, %s', $property->getPattern(), $newPattern));
        } else {
            $property->setPattern($newPattern);
        }
    }
}
