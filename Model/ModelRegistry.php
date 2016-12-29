<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Model;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use EXSyst\Component\Swagger\Items;
use EXSyst\Component\Swagger\Schema;
use EXSyst\Component\Swagger\Swagger;
use Symfony\Component\PropertyInfo\Type;

final class ModelRegistry
{
    private $modelDescribers = [];
    private $options;
    private $unregistered = [];
    private $hashes = [];

    /**
     * @param ModelDescriberInterface[] $modelDescribers
     */
    public function __construct(array $modelDescribers = [])
    {
        $this->options = new \SplObjectStorage();
        $this->modelDescribers = $modelDescribers;
    }

    /**
     * @param Schema|Items $schema
     */
    public function register($schema): ModelOptions
    {
        if (!$schema instanceof Schema && !$schema instanceof Items) {
            throw new \LogicException(sprintf('Expected %s or %s, got %s', Schema::class, Items::class, get_class($schema)));
        }

        if (!isset($this->options[$schema])) {
            $this->unregistered[] = $schema;
            $this->options[$schema] = new ModelOptions();
        }

        return $this->options[$schema];
    }

    /**
     * @internal
     */
    public function registerModelsIn(Swagger $api)
    {
        while (count($this->unregistered)) {
            $tmp = [];
            foreach ($this->unregistered as $schema) {
                $options = $this->options[$schema];
                $options->validate();

                $hash = $options->getHash();
                if (isset($this->hashes[$hash])) {
                    $schema->setRef('#/definitions/'.$this->hashes[$hash]);

                    continue;
                }

                if (!isset($tmp[$hash])) {
                    $tmp[$hash] = [$options, [/* schemas */]];
                }
                $tmp[$hash][1][] = $schema;
            }
            $this->unregistered = [];

            foreach ($tmp as $hash => list($options, $schemas)) {
                $baseSchema = new Schema();
                $described = false;
                foreach ($this->modelDescribers as $modelDescriber) {
                    if ($modelDescriber instanceof ModelRegistryAwareInterface) {
                        $modelDescriber->setModelRegistry($this);
                    }
                    if ($modelDescriber->supports($options)) {
                        $described = true;
                        $modelDescriber->describe($baseSchema, $options);

                        break;
                    }
                }

                if (!$described) {
                    throw new \LogicException(sprintf('Schema of type "%s" can\'t be generated, no describer supports it.', $this->typeToString($options->getType())));
                }

                $name = $this->generateModelName($api, $options);
                $this->hashes[$hash] = $name;
                $api->getDefinitions()->set($name, $baseSchema);

                foreach ($schemas as $schema) {
                    $schema->setRef('#/definitions/'.$name);
                }
            }
        }
    }

    public function __clone()
    {
        $this->options = new \SplObjectStorage();
        $this->unregistered = [];
        $this->hashes = [];
    }

    private function generateModelName(Swagger $api, ModelOptions $options): string
    {
        $definitions = $api->getDefinitions();
        $base = $name = $this->getTypeShortName($options->getType());
        $i = 1;
        while ($definitions->has($name)) {
            ++$i;
            $name = $base.$i;
        }

        return $name;
    }

    private function getTypeShortName(Type $type)
    {
        if (null !== $type->getCollectionValueType()) {
            return $this->getTypeShortName($type->getCollectionValueType()).'[]';
        }
        if (Type::BUILTIN_TYPE_OBJECT === $type->getBuiltinType()) {
            return (new \ReflectionClass($type->getClassName()))->getShortName();
        }

        return $type->getBuiltinType();
    }

    private function typeToString(Type $type): string
    {
        if (Type::BUILTIN_TYPE_OBJECT === $type->getBuiltinType()) {
            return $type->getClassName();
        } elseif (Type::BUILTIN_TYPE_ARRAY === $type->getBuiltinType()) {
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
