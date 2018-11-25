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
use Swagger\Annotations\Property;
use Swagger\Annotations\Schema;
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
     *
     * @param \ReflectionProperty $reflectionProperty
     * @param Property            $property
     */
    public function updateProperty(\ReflectionProperty $reflectionProperty, Property $property): void
    {
        $annotations = $this->annotationsReader->getPropertyAnnotations($reflectionProperty);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof Assert\NotBlank || $annotation instanceof Assert\NotNull) {
                // The field is required
                if (null === $this->schema) {
                    continue;
                }

                $propertyName = $this->getSchemaPropertyName($property);
                if (null === $propertyName) {
                    continue;
                }

                $existingRequiredFields = $this->schema->required ?? [];
                $existingRequiredFields[] = $propertyName;

                $this->schema->required = array_values(array_unique($existingRequiredFields));
            } elseif ($annotation instanceof Assert\Length) {
                $property->minLength = $annotation->min;
                $property->maxLength = $annotation->max;
            } elseif ($annotation instanceof Assert\Regex) {
                $this->appendPattern($property, $annotation->getHtmlPattern());
            } elseif ($annotation instanceof Assert\DateTime) {
                $this->appendPattern($property, $annotation->format);
            } elseif ($annotation instanceof Assert\Count) {
                $property->minItems = $annotation->min;
                $property->maxItems = $annotation->max;
            } elseif ($annotation instanceof Assert\Choice) {
                $property->enum = $annotation->callback ? call_user_func(is_array($annotation->callback) ? $annotation->callback : [$reflectionProperty->class, $annotation->callback]) : $annotation->choices;
            } elseif ($annotation instanceof Assert\Expression) {
                $this->appendPattern($property, $annotation->message);
            }
        }
    }

    public function setSchema(Schema $schema): void
    {
        $this->schema = $schema;
    }

    /**
     * Get assigned property name for property schema.
     *
     * @param Schema $property
     *
     * @return string|void|null
     */
    private function getSchemaPropertyName(Schema $property)
    {
        if (null === $this->schema) {
            return null;
        }

        foreach ($this->schema->properties as $schemaProperty) {
            if ($schemaProperty === $property) {
                return $schemaProperty->property;
            }
        }

        return null;
    }

    /**
     * Append the pattern from the constraint to the existing pattern.
     *
     * @param Schema $property
     * @param        $newPattern
     */
    private function appendPattern(Schema $property, $newPattern): void
    {
        if (null === $newPattern) {
            return;
        }

        if (null !== $property->pattern) {
            $property->pattern = sprintf('%s, %s', $property->pattern, $newPattern);
        } else {
            $property->pattern = $newPattern;
        }
    }
}
