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

use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\OpenApiPhp\ModelRegister;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\Util\SetsContextTrait;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;

/**
 * @internal
 */
class OpenApiAnnotationsReader
{
    use SetsContextTrait;

    private ModelRegister $modelRegister;

    /**
     * @param string[] $mediaTypes
     */
    public function __construct(ModelRegistry $modelRegistry, array $mediaTypes)
    {
        $this->modelRegister = new ModelRegister($modelRegistry, $mediaTypes);
    }

    public function updateSchema(\ReflectionClass $reflectionClass, OA\Schema $schema): void
    {
        if (null === $oaSchema = $this->getAttribute($schema->_context, $reflectionClass, OA\Schema::class)) {
            return;
        }

        // Read #[Model] attributes
        $this->modelRegister->__invoke(new Analysis([$oaSchema], Util::createContext()));

        if (!$oaSchema->validate()) {
            return;
        }

        $schema->mergeProperties($oaSchema);
    }

    /**
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     */
    public function getPropertyName($reflection, string $default): string
    {
        if (null === $oaProperty = $this->getAttribute(new Context(), $reflection, OA\Property::class)) {
            return $default;
        }

        return Generator::UNDEFINED !== $oaProperty->property ? $oaProperty->property : $default;
    }

    /**
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     * @param string[]|null                         $serializationGroups
     */
    public function updateProperty($reflection, OA\Property $property, ?array $serializationGroups = null): void
    {
        if (null === $oaProperty = $this->getAttribute($property->_context, $reflection, OA\Property::class)) {
            return;
        }

        // Read #[Model] attributes
        $this->modelRegister->__invoke(new Analysis([$oaProperty], Util::createContext()), $serializationGroups);

        if (!$oaProperty->validate()) {
            return;
        }

        $property->mergeProperties($oaProperty);
    }

    /**
     * @template T of object
     *
     * @param \ReflectionClass|\ReflectionProperty|\ReflectionMethod $reflection
     * @param class-string<T>                                        $className
     *
     * @return T|null
     */
    private function getAttribute(Context $parentContext, $reflection, string $className)
    {
        $this->setContextFromReflection($parentContext, $reflection);

        try {
            if (null !== $attribute = $reflection->getAttributes($className, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? null) {
                return $attribute->newInstance();
            }
        } finally {
            $this->setContext(null);
        }

        return null;
    }
}
