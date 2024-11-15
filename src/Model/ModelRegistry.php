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
     * @var Model[]
     */
    private array $alternativeNames = [];

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
        foreach (array_reverse($alternativeNames) as $alternativeName => $criteria) {
            $this->alternativeNames[] = $model = new Model(
                new Type('object', false, $criteria['type']),
                $criteria['groups'],
                $criteria['options'] ?? [],
                $criteria['serializationContext'] ?? [],
            );
            $this->names[$model->getHash()] = $alternativeName;
            $this->registeredModelNames[$alternativeName] = $model;
            Util::getSchema($this->api, $alternativeName);
        }
    }

    public function register(Model $model): string
    {
        $hash = $model->getHash();
        if (!isset($this->models[$hash])) {
            $this->models[$hash] = $model;
            $this->unregistered[] = $hash;
        }
        if (!isset($this->names[$hash])) {
            $this->names[$hash] = $this->generateModelName($model);
            $this->registeredModelNames[$this->names[$hash]] = $model;
        }

        // Reserve the name
        Util::getSchema($this->api, $this->names[$hash]);

        return OA\Components::SCHEMA_REF.$this->names[$hash];
    }

    /**
     * @internal
     */
    public function registerSchemas(): void
    {
        while (\count($this->unregistered)) {
            $tmp = [];
            foreach ($this->unregistered as $hash) {
                $tmp[$this->names[$hash]] = $this->models[$hash];
            }
            $this->unregistered = [];

            foreach ($tmp as $name => $model) {
                $schema = null;
                foreach ($this->modelDescribers as $modelDescriber) {
                    if ($modelDescriber instanceof ModelRegistryAwareInterface) {
                        $modelDescriber->setModelRegistry($this);
                    }
                    if ($modelDescriber->supports($model)) {
                        $schema = Util::getSchema($this->api, $name);
                        $modelDescriber->describe($model, $schema);

                        break;
                    }
                }

                if (null === $schema) {
                    $errorMessage = \sprintf('Schema of type "%s" can\'t be generated, no describer supports it.', $this->typeToString($model->getType()));
                    if (Type::BUILTIN_TYPE_OBJECT === $model->getType()->getBuiltinType() && !class_exists($className = $model->getType()->getClassName())) {
                        $errorMessage .= \sprintf(' Class "\\%s" does not exist, did you forget a use statement, or typed it wrong?', $className);
                    }
                    throw new \LogicException($errorMessage);
                }
            }
        }

        if ([] === $this->unregistered && [] !== $this->alternativeNames) {
            foreach ($this->alternativeNames as $model) {
                $this->register($model);
            }
            $this->alternativeNames = [];
            $this->registerSchemas();
        }
    }

    private function generateModelName(Model $model): string
    {
        $name = $base = $this->getTypeShortName($model->getType());
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
