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

use EXSyst\Component\Swagger\Schema;
use EXSyst\Component\Swagger\Swagger;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use Symfony\Component\PropertyInfo\Type;

final class ModelRegistry
{
    private $unregistered = [];
    private $models = [];
    private $names = [];
    private $modelDescribers = [];
    private $api;

    /**
     * @param ModelDescriberInterface[] $modelDescribers
     *
     * @internal
     */
    public function __construct(array $modelDescribers, Swagger $api)
    {
        $this->modelDescribers = $modelDescribers;
        $this->api = $api;
    }

    public function register(Model $model): string
    {
        $hash = $model->getHash();
        if (isset($this->names[$hash])) {
            return '#/definitions/'.$this->names[$hash];
        }

        $this->names[$hash] = $name = $this->generateModelName($model);
        $this->models[$hash] = $model;
        $this->unregistered[] = $hash;

        // Reserve the name
        $this->api->getDefinitions()->get($name);

        return '#/definitions/'.$name;
    }

    /**
     * @internal
     */
    public function registerDefinitions()
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
                        $schema = new Schema();
                        $modelDescriber->describe($model, $schema);

                        break;
                    }
                }

                if (null === $schema) {
                    throw new \LogicException(sprintf('Schema of type "%s" can\'t be generated, no describer supports it.', $this->typeToString($model->getType())));
                }

                $this->api->getDefinitions()->set($name, $schema);
            }
        }
    }

    private function generateModelName(Model $model): string
    {
        $definitions = $this->api->getDefinitions();
        $base = $name = $this->getTypeShortName($model->getType());
        $i = 1;
        while ($definitions->has($name)) {
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
