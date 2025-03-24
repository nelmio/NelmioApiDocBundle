<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\ModelDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\Annotations\AnnotationsReader;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\PropertyDescriber\PropertyDescriberInterface;
use Nelmio\ApiDocBundle\TypeDescriber\TypeDescriberInterface;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\AdvancedNameConverterInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\TypeInfo\Type;

class ObjectModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{
    use ApplyOpenApiDiscriminatorTrait;
    use ModelRegistryAwareTrait;

    private PropertyInfoExtractorInterface $propertyInfo;
    private ?ClassMetadataFactoryInterface $classMetadataFactory;
    private PropertyDescriberInterface|TypeDescriberInterface $propertyDescriber;
    /** @var string[] */
    private array $mediaTypes;
    /** @var (NameConverterInterface&AdvancedNameConverterInterface)|null */
    private ?NameConverterInterface $nameConverter;
    private bool $useValidationGroups;

    /**
     * @param (NameConverterInterface&AdvancedNameConverterInterface)|null $nameConverter
     * @param string[]                                                     $mediaTypes
     */
    public function __construct(
        PropertyInfoExtractorInterface $propertyInfo,
        PropertyDescriberInterface|TypeDescriberInterface $propertyDescribers,
        array $mediaTypes,
        ?NameConverterInterface $nameConverter = null,
        bool $useValidationGroups = false,
        ?ClassMetadataFactoryInterface $classMetadataFactory = null,
    ) {
        $this->propertyInfo = $propertyInfo;
        $this->propertyDescriber = $propertyDescribers;
        $this->mediaTypes = $mediaTypes;
        $this->nameConverter = $nameConverter;
        $this->useValidationGroups = $useValidationGroups;
        $this->classMetadataFactory = $classMetadataFactory;
    }

    public function describe(Model $model, OA\Schema $schema): void
    {
        $class = $model->getType()->getClassName();
        $schema->_context->class = $class;

        $context = ['serializer_groups' => null];
        if (null !== $model->getGroups()) {
            $context['serializer_groups'] = array_filter($model->getGroups(), 'is_string');
        }

        $reflClass = new \ReflectionClass($class);
        $annotationsReader = new AnnotationsReader(
            $this->modelRegistry,
            $this->mediaTypes,
            $this->useValidationGroups
        );
        $classResult = $annotationsReader->updateDefinition($reflClass, $schema);

        if (!$classResult) {
            return;
        }

        $schema->type = 'object';

        $mapping = false;
        if (null !== $this->classMetadataFactory) {
            $mapping = $this->classMetadataFactory
                ->getMetadataFor($class)
                ->getClassDiscriminatorMapping();
        }

        if ($mapping && Generator::UNDEFINED === $schema->discriminator) {
            $this->applyOpenApiDiscriminator(
                $model,
                $schema,
                $this->modelRegistry,
                $mapping->getTypeProperty(),
                $mapping->getTypesMapping()
            );
        }

        $propertyInfoProperties = $this->propertyInfo->getProperties($class, $context);

        if (null === $propertyInfoProperties) {
            return;
        }

        // Fix for https://github.com/nelmio/NelmioApiDocBundle/issues/1756
        // The SerializerExtractor does expose private/protected properties for some reason, so we eliminate them here
        $propertyInfoProperties = array_intersect($propertyInfoProperties, $this->propertyInfo->getProperties($class, []) ?? []);

        foreach ($propertyInfoProperties as $propertyName) {
            $serializedName = null !== $this->nameConverter ? $this->nameConverter->normalize($propertyName, $class, null, $model->getSerializationContext()) : $propertyName;

            $reflections = $this->getReflections($reflClass, $propertyName);

            if (!$annotationsReader->shouldDescribeProperty($reflections)) {
                continue;
            }

            // Check if a custom name is set
            foreach ($reflections as $reflection) {
                $serializedName = $annotationsReader->getPropertyName($reflection, $serializedName);
            }

            $property = Util::getProperty($schema, $serializedName);

            // Interpret additional options
            $groups = $model->getGroups();
            if (isset($groups[$propertyName]) && \is_array($groups[$propertyName])) {
                $groups = $model->getGroups()[$propertyName];
            }
            foreach ($reflections as $reflection) {
                $annotationsReader->updateProperty($reflection, $property, $groups);
            }

            // If type manually defined
            if (Generator::UNDEFINED !== $property->type || Generator::UNDEFINED !== $property->ref) {
                continue;
            }

            if ($this->propertyDescriber instanceof TypeDescriberInterface) {
                $types = $this->propertyInfo->getType($class, $propertyName);
            } else {
                $types = $this->propertyInfo->getTypes($class, $propertyName);
            }

            if (null === $types) {
                throw new \LogicException(\sprintf('The PropertyInfo component was not able to guess the type of %s::$%s. You may need to add a `@var` annotation or use `#[OA\Property(type="")]` to make its type explicit.', $class, $propertyName));
            }

            $this->describeProperty($types, $model, $property, $propertyName);
        }

        $this->markRequiredProperties($schema);
    }

    /**
     * @return \ReflectionProperty[]|\ReflectionMethod[]
     */
    private function getReflections(\ReflectionClass $reflClass, string $propertyName): array
    {
        $reflections = [];
        if ($reflClass->hasProperty($propertyName)) {
            $reflections[] = $reflClass->getProperty($propertyName);
        }

        $camelProp = $this->camelize($propertyName);
        foreach (['', 'get', 'is', 'has', 'can', 'add', 'remove', 'set'] as $prefix) {
            if ($reflClass->hasMethod($prefix.$camelProp)) {
                $reflections[] = $reflClass->getMethod($prefix.$camelProp);
            }
        }

        return $reflections;
    }

    /**
     * Camelizes a given string.
     */
    private function camelize(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    /**
     * @param LegacyType[]|Type $types
     */
    private function describeProperty(array|Type $types, Model $model, OA\Schema $property, string $propertyName): void
    {
        if ($this->propertyDescriber instanceof ModelRegistryAwareInterface) {
            $this->propertyDescriber->setModelRegistry($this->modelRegistry);
        }
        if ($this->propertyDescriber->supports($types, $model->getSerializationContext())) {
            $this->propertyDescriber->describe($types, $property, $model->getSerializationContext());

            return;
        }

        throw new \Exception(\sprintf('Type "%s" is not supported in %s::$%s. You may need to use the `#[OA\Property(type="")]` attribute to specify it manually.', \is_array($types) ? $types[0]->getBuiltinType() : $types, $model->getType()->getClassName(), $propertyName));
    }

    /**
     * Mark properties as required while ordering them in the same order as the properties of the schema.
     * Then append the original required properties.
     */
    private function markRequiredProperties(OA\Schema $schema): void
    {
        if (Generator::isDefault($properties = $schema->properties)) {
            return;
        }

        $newRequired = [];
        foreach ($properties as $property) {
            if (\is_array($schema->required) && \in_array($property->property, $schema->required, true)) {
                $newRequired[] = $property->property;
                continue;
            }

            if (true === $property->nullable || !Generator::isDefault($property->default)) {
                continue;
            }
            $newRequired[] = $property->property;
        }

        if ([] !== $newRequired) {
            $originalRequired = Generator::isDefault($schema->required) ? [] : $schema->required;

            $schema->required = array_values(array_unique(array_merge($newRequired, $originalRequired)));
        }
    }

    public function supports(Model $model): bool
    {
        return LegacyType::BUILTIN_TYPE_OBJECT === $model->getType()->getBuiltinType()
            && (class_exists($model->getType()->getClassName()) || interface_exists($model->getType()->getClassName()));
    }
}
