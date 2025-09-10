<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Model;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\PropertyInfo\Type;

final class ModelRegistry
{
    use LoggerAwareTrait;

    /**
     * @var array<string, Model> List of model names to models
     */
    private array $registeredModelNames = [];

    /**
     * @var string[] List of hashes of models that have not been registered yet
     */
    private array $unregistered = [];

    /**
     * @var array<string, Model> List of model hashes to models
     */
    private array $models = [];

    /**
     * @var array<string, string> List of model hashes to model names
     */
    private array $names = [];

    /**
     * @var array<string, string> List of model hashes to model alternative names
     */
    private array $alternativeNames = [];

    /**
     * @var array<string, array<string, Model>> Map from identifier and schema content to Model
     */
    private array $schemaToModelMap = [];

    /**
     * @var iterable<ModelDescriberInterface>
     */
    private iterable $modelDescribers;

    private OA\OpenApi $api;

    /**
     * @param ModelDescriberInterface[]|iterable $modelDescribers
     * @param array<string, mixed>               $alternativeNames
     *
     * @internal
     */
    public function __construct($modelDescribers, OA\OpenApi $api, array $alternativeNames = [])
    {
        $this->modelDescribers = $modelDescribers;
        $this->api = $api;
        $this->logger = new NullLogger();

        foreach ($alternativeNames as $alternativeName => $criteria) {
            $model = new Model(
                new Type('object', false, $criteria['type']),
                $criteria['groups'],
                $criteria['options'] ?? [],
                $criteria['serializationContext'] ?? [],
            );

            $this->alternativeNames[$model->getHash()] = $alternativeName;

            $this->register($model);
        }
    }

    public function register(Model $model): string
    {
        $hash = $model->getHash();

        $identifier = $this->determineModelName($model);

        $schema = null;
        if (!isset($this->names[$hash])) {
            $this->names[$hash] = $name = $this->generateUniqueModelName($model);
            $this->registeredModelNames[$name] = $model;

            $schema = $this->describeSchema($model, null);
            // Only try to match schemas if we successfully got one
            if (null !== $schema) {
                $schemaJson = json_encode($schema);
                if (isset($this->schemaToModelMap[$identifier][$schemaJson])) {
                    $existingModel = $this->schemaToModelMap[$identifier][$schemaJson];
                    $newHash = $existingModel->getHash();
                    unset($this->names[$hash], $this->registeredModelNames[$name]);

                    return OA\Components::SCHEMA_REF.$this->names[$newHash];
                }
            }
        }

        if (!isset($this->models[$hash])) {
            $this->models[$hash] = $model;
            $this->unregistered[] = $hash;
            $this->unregistered = array_unique($this->unregistered);
            // Only store schema if it was successfully generated
            if (null !== $schema) {
                $this->schemaToModelMap[$identifier][$schemaJson ?? json_encode($schema)] = $model;
            }
        }

        // Reserve the name
        Util::getSchema($this->api, $this->names[$hash]);

        return OA\Components::SCHEMA_REF.$this->names[$hash];
    }

    /**
     * Describe the Model using the first modelDescriber that supports it.
     *
     * @param string|null $name If a name is given, the schema will be registered
     *
     * @return OA\Schema|null If no modelDescriber supports the schema, null is returned ; otherwise an instance of OA\Schema
     */
    private function describeSchema(Model $model, ?string $name): ?OA\Schema
    {
        foreach ($this->modelDescribers as $modelDescriber) {
            if ($modelDescriber instanceof ModelRegistryAwareInterface) {
                $modelDescriber->setModelRegistry($this);
            }
            if ($modelDescriber->supports($model)) {
                if (null === $name) {
                    $schema = new OA\Schema([]);
                } else {
                    $schema = Util::getSchema($this->api, $name);
                }
                $modelDescriber->describe($model, $schema);

                return $schema;
            }
        }

        return null;
    }

    /**
     * @internal
     */
    public function registerSchemas(): void
    {
        while (\count($this->unregistered)) {
            $unregistered = $this->unregistered;
            $this->unregistered = [];

            foreach ($unregistered as $hash) {
                $name = $this->names[$hash];
                $model = $this->models[$hash];
                $schema = $this->describeSchema($model, $name);

                if (null === $schema) {
                    $errorMessage = \sprintf('Schema of type "%s" can\'t be generated, no describer supports it.', $this->typeToString($model->getType()));
                    if (Type::BUILTIN_TYPE_OBJECT === $model->getType()->getBuiltinType() && !class_exists($className = $model->getType()->getClassName())) {
                        $errorMessage .= \sprintf(' Class "\\%s" does not exist, did you forget a use statement, or typed it wrong?', $className);
                    }
                    throw new \LogicException($errorMessage);
                }
            }
        }
    }

    private function determineModelName(Model $model): string
    {
        $hash = $model->getHash();

        // 1. From the alternative names configuration
        if (isset($this->alternativeNames[$hash])) {
            return $this->alternativeNames[$hash];
        }

        // 2. From the model itself (e.g. #[Model(name: "MyModel")])
        if (null !== $model->name) {
            return $model->name;
        }

        // 3. Generate from the type
        return $this->getTypeShortName($model->getType());
    }

    private function generateUniqueModelName(Model $model): string
    {
        $name = $base = $this->determineModelName($model);
        $names = array_column(
            $this->api->components instanceof OA\Components && \is_array($this->api->components->schemas) ? $this->api->components->schemas : [],
            'schema'
        );
        $i = 1;
        while (\in_array($name, $names, true)) {
            if (isset($this->registeredModelNames[$name])) {
                $this->logger->info(\sprintf('Can not assign a name for the model, the name "%s" has already been taken.', $name), [
                    'model' => $this->modelToArray($model),
                    'taken_by' => $this->modelToArray($this->registeredModelNames[$name]),
                ]);
            }
            ++$i;
            $name = $base.$i;
        }

        return $name;
    }

    /**
     * @return array<string, mixed>
     */
    private function modelToArray(Model $model): array
    {
        $getType = function (Type $type) use (&$getType): array {
            return [
                'class' => $type->getClassName(),
                'built_in_type' => $type->getBuiltinType(),
                'nullable' => $type->isNullable(),
                'collection' => $type->isCollection(),
                'collection_key_types' => $type->isCollection() ? array_map($getType, $type->getCollectionKeyTypes()) : null,
                'collection_value_types' => $type->isCollection() ? array_map($getType, $type->getCollectionValueTypes()) : null,
            ];
        };

        return [
            'type' => $getType($model->getType()),
            'options' => $model->getOptions(),
            'groups' => $model->getGroups(),
            'serialization_context' => $model->getSerializationContext(),
        ];
    }

    private function getTypeShortName(Type $type): string
    {
        if (null !== $collectionType = $this->getCollectionValueType($type)) {
            return $this->getTypeShortName($collectionType).'[]';
        }

        if (Type::BUILTIN_TYPE_OBJECT === $type->getBuiltinType()) {
            $parts = explode('\\', $type->getClassName());

            return end($parts);
        }

        return $type->getBuiltinType();
    }

    private function typeToString(Type $type): string
    {
        if (Type::BUILTIN_TYPE_OBJECT === $type->getBuiltinType()) {
            return '\\'.$type->getClassName();
        } elseif ($type->isCollection()) {
            if (null !== $collectionType = $this->getCollectionValueType($type)) {
                return $this->typeToString($collectionType).'[]';
            } else {
                return 'mixed[]';
            }
        } else {
            return $type->getBuiltinType();
        }
    }

    private function getCollectionValueType(Type $type): ?Type
    {
        return $type->getCollectionValueTypes()[0] ?? null;
    }
}
