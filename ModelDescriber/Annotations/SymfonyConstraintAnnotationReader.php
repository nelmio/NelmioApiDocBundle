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
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraint;
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
     *
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     */
    public function updateProperty($reflection, OA\Property $property): void
    {
        foreach ($this->getAnnotations($reflection) as $outerAnnotation) {
            $innerAnnotations = $outerAnnotation instanceof Assert\Compound
                ? $outerAnnotation->constraints
                : [$outerAnnotation];

            foreach ($innerAnnotations as $innerAnnotation) {
                if ($innerAnnotation instanceof Assert\NotBlank || $innerAnnotation instanceof Assert\NotNull) {
                    // To support symfony/validator < 4.3
                    if ($innerAnnotation instanceof Assert\NotBlank && \property_exists($innerAnnotation, 'allowNull') && $innerAnnotation->allowNull) {
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
                } elseif ($innerAnnotation instanceof Assert\Length) {
                    if (isset($innerAnnotation->min)) {
                        $property->minLength = (int) $innerAnnotation->min;
                    }
                    if (isset($innerAnnotation->max)) {
                        $property->maxLength = (int) $innerAnnotation->max;
                    }
                } elseif ($innerAnnotation instanceof Assert\Regex) {
                    $this->appendPattern($property, $innerAnnotation->getHtmlPattern());
                } elseif ($innerAnnotation instanceof Assert\Count) {
                    if (isset($innerAnnotation->min)) {
                        $property->minItems = (int) $innerAnnotation->min;
                    }
                    if (isset($innerAnnotation->max)) {
                        $property->maxItems = (int) $innerAnnotation->max;
                    }
                } elseif ($innerAnnotation instanceof Assert\Choice) {
                    $this->applyEnumFromChoiceConstraint($property, $innerAnnotation, $reflection);
                } elseif ($innerAnnotation instanceof Assert\Range) {
                    if (isset($innerAnnotation->min)) {
                        $property->minimum = (int) $innerAnnotation->min;
                    }
                    if (isset($innerAnnotation->max)) {
                        $property->maximum = (int) $innerAnnotation->max;
                    }
                } elseif ($innerAnnotation instanceof Assert\LessThan) {
                    $property->exclusiveMaximum = true;
                    $property->maximum = (int) $innerAnnotation->value;
                } elseif ($innerAnnotation instanceof Assert\LessThanOrEqual) {
                    $property->maximum = (int) $innerAnnotation->value;
                } elseif ($innerAnnotation instanceof Assert\GreaterThan) {
                    $property->exclusiveMinimum = true;
                    $property->minimum = (int) $innerAnnotation->value;
                } elseif ($innerAnnotation instanceof Assert\GreaterThanOrEqual) {
                    $property->minimum = (int) $innerAnnotation->value;
                }
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

    /**
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     */
    private function applyEnumFromChoiceConstraint(OA\Schema $property, Assert\Choice $choice, $reflection): void
    {
        if ($choice->callback) {
            $enumValues = call_user_func(is_array($choice->callback) ? $choice->callback : [$reflection->class, $choice->callback]);
        } else {
            $enumValues = $choice->choices;
        }

        $setEnumOnThis = $property;
        if ($choice->multiple) {
            $setEnumOnThis = Util::getChild($property, OA\Items::class);
        }

        $setEnumOnThis->enum = array_values($enumValues);
    }

    /**
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     */
    private function getAnnotations($reflection): \Traversable
    {
        if (\PHP_VERSION_ID >= 80000) {
            foreach ($reflection->getAttributes(Constraint::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                yield $attribute->newInstance();
            }
        }

        if ($reflection instanceof \ReflectionProperty) {
            yield from $this->annotationsReader->getPropertyAnnotations($reflection);
        } elseif ($reflection instanceof \ReflectionMethod) {
            yield from $this->annotationsReader->getMethodAnnotations($reflection);
        }
    }
}
