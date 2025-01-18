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

use Nelmio\ApiDocBundle\Util\SetsContextTrait;
use OpenApi\Annotations as OA;
use OpenApi\Generator;

/**
 * Read default values of a property from the function or property signature.
 *
 * This needs to be called before the {@see SymfonyConstraintAnnotationReader},
 * otherwise required properties might be considered wrongly.
 *
 * @internal
 */
final class ReflectionReader
{
    use SetsContextTrait;

    private ?OA\Schema $schema;

    /**
     * Update the given property and schema with defined Symfony constraints.
     *
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     */
    public function updateProperty(
        $reflection,
        OA\Property $property,
    ): void {
        // The default has been set by an Annotation or Attribute
        // We leave that as it is!
        if (Generator::UNDEFINED !== $property->default) {
            return;
        }

        $serializedName = $reflection->getName();
        foreach (['get', 'is', 'has', 'can', 'add', 'remove', 'set'] as $prefix) {
            if (str_starts_with($serializedName, $prefix)) {
                $serializedName = substr($serializedName, \strlen($prefix));
            }
        }

        if ($reflection instanceof \ReflectionMethod) {
            $methodDefault = $this->getDefaultFromMethodReflection($reflection);
            if (Generator::UNDEFINED !== $methodDefault) {
                if (null === $methodDefault) {
                    $property->nullable = true;

                    return;
                }
                $property->default = $methodDefault;

                return;
            }
        }

        if ($reflection instanceof \ReflectionProperty) {
            $propertyDefault = $this->getDefaultFromPropertyReflection($reflection);
            if (Generator::UNDEFINED !== $propertyDefault) {
                if (Generator::UNDEFINED === $property->nullable && null === $propertyDefault) {
                    $property->nullable = true;

                    return;
                }
                $property->default = $propertyDefault;

                return;
            }
        }
        // Fix for https://github.com/nelmio/NelmioApiDocBundle/issues/2222
        // Promoted properties with a value initialized by the constructor are not considered to have a default value
        // and are therefore not returned by ReflectionClass::getDefaultProperties(); see https://bugs.php.net/bug.php?id=81386
        $reflClassConstructor = $reflection->getDeclaringClass()->getConstructor();
        $reflClassConstructorParameters = null !== $reflClassConstructor ? $reflClassConstructor->getParameters() : [];
        foreach ($reflClassConstructorParameters as $parameter) {
            if ($parameter->name !== $serializedName) {
                continue;
            }

            if (!$parameter->isDefaultValueAvailable()) {
                continue;
            }

            if (null === $this->schema) {
                continue;
            }

            if (!Generator::isDefault($property->default)) {
                continue;
            }

            $default = $parameter->getDefaultValue();
            if (Generator::UNDEFINED === $property->nullable && null === $default) {
                $property->nullable = true;
            }

            $property->default = $default;
        }
    }

    public function setSchema(OA\Schema $schema): void
    {
        $this->schema = $schema;
    }

    /**
     * @return mixed|string
     */
    private function getDefaultFromMethodReflection(\ReflectionMethod $reflection)
    {
        if (!str_starts_with($reflection->name, 'set')) {
            return Generator::UNDEFINED;
        }

        if (1 !== $reflection->getNumberOfParameters()) {
            return Generator::UNDEFINED;
        }

        $param = $reflection->getParameters()[0];

        if (!$param->isDefaultValueAvailable()) {
            return Generator::UNDEFINED;
        }

        if (null === $param->getDefaultValue()) {
            return Generator::UNDEFINED;
        }

        return $param->getDefaultValue();
    }

    /**
     * @return mixed|string
     */
    public function getDefaultFromPropertyReflection(\ReflectionProperty $reflection)
    {
        $propertyName = $reflection->name;
        if (!$reflection->getDeclaringClass()->hasProperty($propertyName)) {
            return Generator::UNDEFINED;
        }

        if (!$reflection->hasDefaultValue()) {
            return Generator::UNDEFINED;
        }

        if ($this->hasImplicitNullDefaultValue($reflection)) {
            return Generator::UNDEFINED;
        }

        return $reflection->getDefaultValue();
    }

    /**
     * Check whether the default value is an implicit null.
     *
     * An implicit null would be any null value that is not explicitly set and
     * contradicts a set DocBlock @ var annotation
     *
     * So a property without an explicit value but an `@var int` docblock
     * would be considered as not having an implicit null default value as
     * that contradicts the annotation.
     *
     * A property without a default value and no docblock would be considered
     * to have an explicit NULL default value to be set though.
     */
    private function hasImplicitNullDefaultValue(\ReflectionProperty $reflection): bool
    {
        if (null !== $reflection->getDefaultValue()) {
            return false;
        }

        if (false === $reflection->getDocComment()) {
            return true;
        }

        $docComment = $reflection->getDocComment();
        if (!preg_match('/@var\s+([^\s]+)/', $docComment, $matches)) {
            return true;
        }

        return !str_contains(strtolower($matches[1]), 'null');
    }
}
