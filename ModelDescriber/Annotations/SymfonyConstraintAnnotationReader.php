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
use ReflectionProperty;
use Symfony\Component\Validator\Constraints\AbstractComparison;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Expression;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;

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
     * @param ReflectionProperty $reflectionProperty
     * @param Schema             $property
     */
    public function updateProperty(ReflectionProperty $reflectionProperty, Schema $property)
    {
        $annotations = $this->annotationsReader->getPropertyAnnotations($reflectionProperty);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof NotBlank || $annotation instanceof NotNull) {
                $this->updateSchemaDefinitionWithRequiredProperty($reflectionProperty);
            }

            if ($annotation instanceof Length) {
                if ($annotation->min > 0) {
                    $this->updateSchemaDefinitionWithRequiredProperty($reflectionProperty);
                }

                $property->setMinLength($annotation->min);
                $property->setMaxLength($annotation->max);
            }

            if ($annotation instanceof Regex) {
                $this->appendPattern($property, $annotation->getHtmlPattern());
            }

            if ($annotation instanceof AbstractComparison) {
                $this->appendDescription($property, $annotation->message);
            }

            if ($annotation instanceof DateTime) {
                $this->appendPattern($property, $annotation->format);
            }

            if ($annotation instanceof Count) {
                $property->setMinItems($annotation->min);
                $property->setMaxItems($annotation->max);
            }

            if ($annotation instanceof Choice) {
                $property->setEnum($annotation->choices);
            }

            if ($annotation instanceof Expression) {
                $this->appendPattern($property, $annotation->message);
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * Set the required properties on the scheme
     *
     * @param ReflectionProperty $reflectionProperty
     */
    private function updateSchemaDefinitionWithRequiredProperty(ReflectionProperty $reflectionProperty)
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
     *
     * @param Schema $property
     * @param string $newPattern
     */
    private function appendPattern(Schema $property, string $newPattern)
    {
        if (null !== $property->getPattern()) {
            $property->setPattern(sprintf('%s, %s', $property->getPattern(), $newPattern));
        } else {
            $property->setPattern($newPattern);
        }
    }

    /**
     * Append the description from the constraint to the existing description.
     *
     * @param Schema $property
     * @param string $newDescription
     */
    private function appendDescription(Schema $property, string $newDescription)
    {
        if (null !== $property->getDescription()) {
            $property->setDescription(sprintf('%s, %s', $property->getDescription(), $newDescription));
        } else {
            $property->setDescription($newDescription);
        }
    }
}
