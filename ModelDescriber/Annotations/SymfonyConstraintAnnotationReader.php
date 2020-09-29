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
use OpenApi\Annotations as OA;
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
     * @var OA\Schema
     */
    private $schema;

    public function __construct(Reader $annotationsReader)
    {
        $this->annotationsReader = $annotationsReader;
    }

    /**
     * Update the given property and schema with defined Symfony constraints.
     */
    public function updateProperty($reflection, OA\Property $property): void
    {
        if ($reflection instanceof \ReflectionProperty) {
            $annotations = $this->annotationsReader->getPropertyAnnotations($reflection);
        } else {
            $annotations = $this->annotationsReader->getMethodAnnotations($reflection);
        }

        foreach ($annotations as $annotation) {
            if ($annotation instanceof Assert\NotBlank || $annotation instanceof Assert\NotNull) {
                // To support symfony/validator < 4.3
                if ($annotation instanceof Assert\NotBlank && \property_exists($annotation, 'allowNull') && $annotation->allowNull) {
                    // The field is optional
                    continue;
                }

                // The field is required
                if (null === $this->schema) {
                    continue;
                }

                $propertyName = $this->getSchemaPropertyName($property);
                if (null === $propertyName) {
                    continue;
                }

                $existingRequiredFields =  OA\UNDEFINED !== $this->schema->required ? $this->schema->required : [];
                $existingRequiredFields[] = $propertyName;

                $this->schema->required = array_values(array_unique($existingRequiredFields));
            } elseif ($annotation instanceof Assert\Length) {
                $property->minLength = (int) $annotation->min;
                $property->maxLength = (int) $annotation->max;
            } elseif ($annotation instanceof Assert\Regex) {
                $this->appendPattern($property, $annotation->getHtmlPattern());
            } elseif ($annotation instanceof Assert\Count) {
                $property->minItems = (int) $annotation->min;
                $property->maxItems = (int) $annotation->max;
            } elseif ($annotation instanceof Assert\Choice) {
                $values = $annotation->callback ? call_user_func(is_array($annotation->callback) ? $annotation->callback : [$reflection->class, $annotation->callback]) : $annotation->choices;
                $property->enum = array_values($values);
            } elseif ($annotation instanceof Assert\Range) {
                $property->minimum = (int) $annotation->min;
                $property->maximum = (int) $annotation->max;
            } elseif ($annotation instanceof Assert\LessThan) {
                $property->exclusiveMaximum = true;
                $property->maximum = (int) $annotation->value;
            } elseif ($annotation instanceof Assert\LessThanOrEqual) {
                $property->maximum = (int) $annotation->value;
            }
        }
    }

    public function setSchema($schema): void
    {
        $this->schema = $schema;
    }

    /**
     * Get assigned property name for property schema.
     */
    private function getSchemaPropertyName(OA\Schema $property): ?string
    {
        if (null === $this->schema) {
            return null;
        }
        foreach ($this->schema->properties as $schemaProperty) {
            if ($schemaProperty === $property) {
                return OA\UNDEFINED !== $schemaProperty->property ? $schemaProperty->property : null;
            }
        }

        return null;
    }

    /**
     * Append the pattern from the constraint to the existing pattern.
     */
    private function appendPattern(OA\Schema $property, $newPattern): void
    {
        if (null === $newPattern) {
            return;
        }
        if (OA\UNDEFINED !== $property->pattern) {
            $property->pattern = sprintf('%s, %s', $property->pattern, $newPattern);
        } else {
            $property->pattern = $newPattern;
        }
    }
}
