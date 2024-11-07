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
 * This needs to be called before the SymfonyConstraintAnnotationReader,
 * otherwise required properties might be considered wrongly.
 *
 * @internal
 */
class ReflectionReader
{
    use SetsContextTrait;

    /**
     * @var OA\Schema
     */
    private $schema;

    /**
     * Update the given property and schema with defined Symfony constraints.
     *
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     * @param string[]|null                         $validationGroups
     */
    public function updateProperty(
        $reflection,
        OA\Property $property,
        ?array $validationGroups = null
    ): void {
        if (PHP_VERSION_ID < 80000) {
            return;
        }
        // The default has been set by an Annotation or Attribute
        // We leave that as it is!
        if (Generator::UNDEFINED !== $property->default) {
            return;
        }

        $serializedName = $reflection->getName();
        foreach (['', 'get', 'is', 'has', 'can', 'add', 'remove', 'set'] as $prefix) {
            if (0 === strpos($serializedName, $prefix)) {
                $serializedName = substr($serializedName, strlen($prefix));
            }
        }

        if ($reflection instanceof \ReflectionMethod) {
            $methodDefault = $this->getDefaultFromMethodReflection($reflection);
            if (Generator::UNDEFINED !== $methodDefault) {
                $property->default = $methodDefault;

                return;
            }
        }

        if ($reflection instanceof \ReflectionProperty) {
            $methodDefault = $this->getDefaultFromPropertyReflection($reflection);
            if (Generator::UNDEFINED !== $methodDefault) {
                $property->default = $methodDefault;

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

            $property->default = $parameter->getDefaultValue();
        }
    }

    public function setSchema(OA\Schema $schema): void
    {
        $this->schema = $schema;
    }

    private function getDefaultFromMethodReflection(\ReflectionMethod $reflection): mixed
    {
        if (0 !== strpos($reflection->name, 'set')) {
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

    public function getDefaultFromPropertyReflection(\ReflectionProperty $reflection): mixed
    {
        $propertyName = $reflection->name;
        if (!$reflection->getDeclaringClass()->hasProperty($propertyName)) {
            return Generator::UNDEFINED;
        }

        $reflectionProp = $reflection->getDeclaringClass()->getProperty($propertyName);
        if (!$reflectionProp->hasDefaultValue()) {
            return Generator::UNDEFINED;
        }

        if (null === $reflectionProp->getDefaultValue()) {
            return Generator::UNDEFINED;
        }

        return $reflectionProp->getDefaultValue();
    }
}
