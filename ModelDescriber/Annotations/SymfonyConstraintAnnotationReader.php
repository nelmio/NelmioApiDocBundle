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
use OpenApi\Generator;
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

            $this->processPropertyAnnotations($reflection, $property, $innerAnnotations);
        }
    }

    private function processPropertyAnnotations($reflection, OA\Property $property, $annotations)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Assert\NotBlank || $annotation instanceof Assert\NotNull) {
                // To support symfony/validator < 4.3
                if ($annotation instanceof Assert\NotBlank && \property_exists($annotation, 'allowNull') && $annotation->allowNull) {
                    // The field is optional
                    return;
                }

                // The field is required
                if (null === $this->schema) {
                    return;
                }

                $propertyName = $this->getSchemaPropertyName($property);
                if (null === $propertyName) {
                    return;
                }

                $existingRequiredFields =  Generator::UNDEFINED !== $this->schema->required ? $this->schema->required : [];
                $existingRequiredFields[] = $propertyName;

                $this->schema->required = array_values(array_unique($existingRequiredFields));
            } elseif ($annotation instanceof Assert\Length) {
                if (isset($annotation->min)) {
                    $property->minLength = (int) $annotation->min;
                }
                if (isset($annotation->max)) {
                    $property->maxLength = (int) $annotation->max;
                }
            } elseif ($annotation instanceof Assert\Regex) {
                $this->appendPattern($property, $annotation->getHtmlPattern());
            } elseif ($annotation instanceof Assert\Count) {
                if (isset($annotation->min)) {
                    $property->minItems = (int) $annotation->min;
                }
                if (isset($annotation->max)) {
                    $property->maxItems = (int) $annotation->max;
                }
            } elseif ($annotation instanceof Assert\Choice) {
                $this->applyEnumFromChoiceConstraint($property, $annotation, $reflection);
            } elseif ($annotation instanceof Assert\Range) {
                if (isset($annotation->min)) {
                    $property->minimum = (int) $annotation->min;
                }
                if (isset($annotation->max)) {
                    $property->maximum = (int) $annotation->max;
                }
            } elseif ($annotation instanceof Assert\LessThan) {
                $property->exclusiveMaximum = true;
                $property->maximum = (int) $annotation->value;
            } elseif ($annotation instanceof Assert\LessThanOrEqual) {
                $property->maximum = (int) $annotation->value;
            } elseif ($annotation instanceof Assert\GreaterThan) {
                $property->exclusiveMinimum = true;
                $property->minimum = (int) $annotation->value;
            } elseif ($annotation instanceof Assert\GreaterThanOrEqual) {
                $property->minimum = (int) $annotation->value;
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
                return Generator::UNDEFINED !== $schemaProperty->property ? $schemaProperty->property : null;
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
        if (Generator::UNDEFINED !== $property->pattern) {
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
        if (\PHP_VERSION_ID >= 80000 && class_exists(Constraint::class)) {
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
