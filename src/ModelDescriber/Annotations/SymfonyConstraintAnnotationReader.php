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
     * @var OA\Schema
     */
    private $schema;

    private bool $useValidationGroups;

    public function __construct(bool $useValidationGroups = false)
    {
        $this->useValidationGroups = $useValidationGroups;
    }

    /**
     * Update the given property and schema with defined Symfony constraints.
     *
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     * @param string[]|null                         $validationGroups
     */
    public function updateProperty($reflection, OA\Property $property, ?array $validationGroups = null): void
    {
        foreach ($this->getAttributes($property->_context, $reflection, $validationGroups) as $outerAttribute) {
            $innerAttributes = $outerAttribute instanceof Assert\Compound || $outerAttribute instanceof Assert\Sequentially
                ? $outerAttribute->constraints
                : [$outerAttribute];

            $this->processPropertyAttributes($reflection, $property, $innerAttributes);
        }
    }

    /**
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     * @param Constraint[]                          $attributes
     */
    private function processPropertyAttributes($reflection, OA\Property $property, array $attributes): void
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Assert\NotBlank || $attribute instanceof Assert\NotNull) {
                if ($attribute instanceof Assert\NotBlank && $attribute->allowNull) {
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

                if (!Generator::isDefault($property->default)) {
                    return;
                }

                $existingRequiredFields = Generator::UNDEFINED !== $this->schema->required ? $this->schema->required : [];
                $existingRequiredFields[] = $propertyName;

                $this->schema->required = array_values(array_unique($existingRequiredFields));
                $property->nullable = false;
            } elseif ($attribute instanceof Assert\Length) {
                if (isset($attribute->min)) {
                    $property->minLength = $attribute->min;
                }
                if (isset($attribute->max)) {
                    $property->maxLength = $attribute->max;
                }
            } elseif ($attribute instanceof Assert\Regex) {
                $this->appendPattern($property, $attribute->getHtmlPattern());
            } elseif ($attribute instanceof Assert\Count) {
                if (isset($attribute->min)) {
                    $property->minItems = $attribute->min;
                }
                if (isset($attribute->max)) {
                    $property->maxItems = $attribute->max;
                }
            } elseif ($attribute instanceof Assert\Choice) {
                $this->applyEnumFromChoiceConstraint($property, $attribute, $reflection);
            } elseif ($attribute instanceof Assert\Range) {
                if (\is_int($attribute->min)) {
                    $property->minimum = $attribute->min;
                }
                if (\is_int($attribute->max)) {
                    $property->maximum = $attribute->max;
                }
            } elseif ($attribute instanceof Assert\LessThan) {
                if (\is_int($attribute->value)) {
                    $property->exclusiveMaximum = true;
                    $property->maximum = $attribute->value;
                }
            } elseif ($attribute instanceof Assert\LessThanOrEqual) {
                if (\is_int($attribute->value)) {
                    $property->maximum = $attribute->value;
                }
            } elseif ($attribute instanceof Assert\GreaterThan) {
                if (\is_int($attribute->value)) {
                    $property->exclusiveMinimum = true;
                    $property->minimum = $attribute->value;
                }
            } elseif ($attribute instanceof Assert\GreaterThanOrEqual) {
                if (\is_int($attribute->value)) {
                    $property->minimum = $attribute->value;
                }
            }
        }
    }

    public function setSchema(OA\Schema $schema): void
    {
        $this->schema = $schema;
    }

    /**
     * Append the pattern from the constraint to the existing pattern.
     */
    private function appendPattern(OA\Schema $property, ?string $newPattern): void
    {
        if (null === $newPattern) {
            return;
        }
        if (Generator::UNDEFINED !== $property->pattern) {
            $property->pattern = \sprintf('%s, %s', $property->pattern, $newPattern);
        } else {
            $property->pattern = $newPattern;
        }
    }

    /**
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     */
    private function applyEnumFromChoiceConstraint(OA\Schema $property, Assert\Choice $choice, $reflection): void
    {
        if (null !== $choice->callback) {
            $enumValues = \call_user_func(\is_array($choice->callback) ? $choice->callback : [$reflection->class, $choice->callback]);
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
     * @param string[]|null                         $validationGroups
     *
     * @return iterable<Constraint>
     */
    private function getAttributes(Context $parentContext, $reflection, ?array $validationGroups): iterable
    {
        // To correctly load OA attributes
        $this->setContextFromReflection($parentContext, $reflection);

        foreach ($this->locateAttributes($reflection) as $attribute) {
            if (!$this->useValidationGroups || $this->isConstraintInGroup($attribute, $validationGroups)) {
                yield $attribute;
            }
        }

        $this->setContext(null);
    }

    /**
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     *
     * @return \Traversable<Constraint>
     */
    private function locateAttributes($reflection): \Traversable
    {
        if (class_exists(Constraint::class)) {
            foreach ($reflection->getAttributes(Constraint::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                yield $attribute->newInstance();
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
     *
     * @param string[]|null $validationGroups
     */
    private function isConstraintInGroup(Constraint $attribute, ?array $validationGroups): bool
    {
        if (null === $validationGroups) {
            $validationGroups = [Constraint::DEFAULT_GROUP];
        }

        return [] !== array_intersect(
            $validationGroups,
            (array) $attribute->groups
        );
    }
}
