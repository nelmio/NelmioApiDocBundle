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
use Symfony\Component\PropertyInfo\Type;

final class ModelRegistry
{
    private $alternativeNames = [];

    private $unregistered = [];

    private $models = [];

    private $names = [];

    private $modelDescribers = [];

    private $api;

    /**
     * @param ModelDescriberInterface[]|iterable $modelDescribers
     *
     * @internal
     */
    public function __construct($modelDescribers, OA\OpenApi $api, array $alternativeNames = [])
    {
        $this->modelDescribers = $modelDescribers;
        $this->api = $api;

        foreach (array_reverse($alternativeNames) as $alternativeName => $criteria) {
            $this->alternativeNames[] = $model = new Model(new Type('object', false, $criteria['type']), $criteria['groups']);
            $this->names[$model->getHash()] = $alternativeName;
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
        while (count($this->unregistered)) {
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
                    throw new \LogicException(sprintf('Schema of type "%s" can\'t be generated, no describer supports it.', $this->typeToString($model->getType())));
                }
            }
        }

        if (empty($this->unregistered) && !empty($this->alternativeNames)) {
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
            $this->api->components instanceof OA\Components && is_array($this->api->components->schemas) ? $this->api->components->schemas : [],
            'schema'
        );
        $i = 1;
        while (\in_array($name, $names, true)) {
            ++$i;
            $name = $base.$i;
        }

        return $name;
    }

    private function getTypeShortName(Type $type): string
    {
        if (null !== $type->getCollectionValueType()) {
            return $this->getTypeShortName($type->getCollectionValueType()).'[]';
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
            return $type->getClassName();
        } elseif ($type->isCollection()) {
            if (null !== $type->getCollectionValueType()) {
                return $this->typeToString($type->getCollectionValueType()).'[]';
            } else {
                return 'mixed[]';
            }
        } else {
            return $type->getBuiltinType();
        }
    }
}
