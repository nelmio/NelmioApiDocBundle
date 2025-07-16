<?php

namespace Nelmio\ApiDocBundle\Extractor;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Attribute reader for PHP 8+ attributes support
 */
class AttributeReader
{
    /**
     * Gets a method attribute
     *
     * @param \ReflectionMethod $method
     * @param string $attributeName
     * @return object|null
     */
    public function getMethodAttribute(\ReflectionMethod $method, string $attributeName): ?object
    {
        $attributes = $method->getAttributes($attributeName);
        
        if (empty($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    /**
     * Gets all method attributes
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    public function getMethodAttributes(\ReflectionMethod $method): array
    {
        $attributes = array();
        foreach ($method->getAttributes() as $attribute) {
            $attributes[] = $attribute->newInstance();
        }

        return $attributes;
    }

    /**
     * Checks if PHP 8+ attributes are supported
     *
     * @return bool
     */
    public function supportsAttributes(): bool
    {
        return true; // Always true since we require PHP 8.0+
    }
}