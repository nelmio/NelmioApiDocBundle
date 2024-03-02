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
use Nelmio\ApiDocBundle\Util\SetsContextTrait;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @internal
 */
class SymfonyConstraintAnnotationReader
{
    use SetsContextTrait;

    /**
     * @var Reader|null
     */
    private $annotationsReader;

    /**
     * @var OA\Schema
     */
    private $schema;

    /**
     * @var bool
     */
    private $useValidationGroups;

    public function __construct(?Reader $annotationsReader, bool $useValidationGroups = false)
    {
        $this->annotationsReader = $annotationsReader;
        $this->useValidationGroups = $useValidationGroups;
    }

    /**
     * Update the given property and schema with defined Symfony constraints.
     *
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     */
    public function updateProperty($reflection, OA\Property $property, ?array $validationGroups = null): void
    {
        foreach ($this->getAnnotations($property->_context, $reflection, $validationGroups) as $outerAnnotation) {
            $innerAnnotations = $outerAnnotation instanceof Assert\Compound || $outerAnnotation instanceof Assert\Sequentially
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

                $propertyName = Util::getSchemaPropertyName($this->schema, $property);
                if (null === $propertyName) {
                    return;
                }

                $existingRequiredFields = Generator::UNDEFINED !== $this->schema->required ? $this->schema->required : [];
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
                if (\is_int($annotation->min)) {
                    $property->minimum = $annotation->min;
                }
                if (\is_int($annotation->max)) {
                    $property->maximum = $annotation->max;
                }
            } elseif ($annotation instanceof Assert\LessThan) {
                if (\is_int($annotation->value)) {
                    $property->exclusiveMaximum = true;
                    $property->maximum = $annotation->value;
                }
            } elseif ($annotation instanceof Assert\LessThanOrEqual) {
                if (\is_int($annotation->value)) {
                    $property->maximum = $annotation->value;
                }
            } elseif ($annotation instanceof Assert\GreaterThan) {
                if (\is_int($annotation->value)) {
                    $property->exclusiveMinimum = true;
                    $property->minimum = $annotation->value;
                }
            } elseif ($annotation instanceof Assert\GreaterThanOrEqual) {
                if (\is_int($annotation->value)) {
                    $property->minimum = $annotation->value;
                }
            }
        }
    }

    public function setSchema($schema): void
    {
        $this->schema = $schema;
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
    private function getAnnotations(Context $parentContext, $reflection, ?array $validationGroups): iterable
    {
        // To correctly load OA annotations
        $this->setContextFromReflection($parentContext, $reflection);

        foreach ($this->locateAnnotations($reflection) as $annotation) {
            if (!$annotation instanceof Constraint) {
                continue;
            }

            if (!$this->useValidationGroups || $this->isConstraintInGroup($annotation, $validationGroups)) {
                yield $annotation;
            }
        }

        $this->setContext(null);
    }

    /**
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     */
    private function locateAnnotations($reflection): \Traversable
    {
        if (\PHP_VERSION_ID >= 80000 && class_exists(Constraint::class)) {
            foreach ($reflection->getAttributes(Constraint::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                yield $attribute->newInstance();
            }
        }

        if (null !== $this->annotationsReader) {
            if ($reflection instanceof \ReflectionProperty) {
                yield from $this->annotationsReader->getPropertyAnnotations($reflection);
            } elseif ($reflection instanceof \ReflectionMethod) {
                yield from $this->annotationsReader->getMethodAnnotations($reflection);
            }
        }
    }

    /**
     * Check to see if the given constraint is in the provided serialization groups.
     *
     * If no groups are provided the validator would run in the Constraint::DEFAULT_GROUP,
     * and constraints without any `groups` passed to them would be in that same
     * default group. So even with a null $validationGroups passed here there still
     * has to be a check on the default group.
     */
    private function isConstraintInGroup(Constraint $annotation, ?array $validationGroups): bool
    {
        return count(array_intersect(
            $validationGroups ?: [Constraint::DEFAULT_GROUP],
            (array) $annotation->groups
        )) > 0;
    }
}
