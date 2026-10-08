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

        if (!$this->validateAnnotation($oaSchema, $schema)) {
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

        if (!$this->validateAnnotation($oaProperty, $property)) {
            return;
        }

        $property->mergeProperties($oaProperty);
    }

    /**
     * swagger-php 6 validate() checks 3.1 keywords against its version argument, which defaults to 3.0.0.
     * swagger-php 5 (and early 6.0) has no such argument and reads the version from the context instead.
     */
    private function validateAnnotation(OA\AbstractAnnotation $annotation, OA\AbstractAnnotation $target): bool
    {
        $version = $this->configuredOpenApiVersion($target);
        if (null === $version || !self::validateAcceptsOpenApiVersion()) {
            return $annotation->validate();
        }

        // Named argument passed through reflection so the call stays valid on every supported swagger-php major.
        return (bool) (new \ReflectionMethod($annotation, 'validate'))->invokeArgs($annotation, ['version' => $version]);
    }

    private function configuredOpenApiVersion(OA\AbstractAnnotation $annotation): ?string
    {
        $version = $annotation->_context->version ?? null;
        if (!\is_string($version) || '' === $version) {
            return null;
        }

        return $version;
    }

    private static function validateAcceptsOpenApiVersion(): bool
    {
        static $accepts;

        if (null === $accepts) {
            $accepts = false;
            foreach ((new \ReflectionMethod(OA\AbstractAnnotation::class, 'validate'))->getParameters() as $parameter) {
                if ('version' === $parameter->getName()) {
                    $accepts = true;
                    break;
                }
            }
        }

        return $accepts;
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
