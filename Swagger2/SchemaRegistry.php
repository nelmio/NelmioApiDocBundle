<?php

namespace Nelmio\ApiDocBundle\Swagger2;

use Nelmio\ApiDocBundle\Swagger2\Segment\Schema;

class SchemaRegistry
{
    /**
     * @var Schema[]
     */
    protected $schemas = array();

    public function register($className, array $parameters)
    {
        $name = $this->createName($className);

        if (isset($this->schemas[$name])) {
            return $this->schemas[$name];
        }

        $schemaProperties = array();

        if (is_array($parameters)) {
            foreach ($parameters as $name => $parameter)
            {
                if (!isset($parameters['type'])) {
                    continue;
                }
                var_dump($name);
                var_dump($parameter);
                $property = new Segment\Parameter\SchemaProperty($name);
                $schemaProperties[] = $property;

                switch ($parameter['actualType']) {
                case DataTypes::MODEL:
                    if (isset($parameter['children'])) {
                        $property->setSchema(
                            $this->register(
                                $parameter['subType'],
                                isset($parameter['children']) ? $parameter['children'] : null
                            )
                        );
                    }
                    break;
                case DataTypes::COLLECTION:
                    if (isset($parameter['children'])) {
                        $property->setSchema(
                            $this->register(
                                $parameter['subType'],
                                isset($parameter['children']) ? $parameter['children'] : null
                            )
                        );
                    }
                    $property->setCollection(true);
                    break;
                }
            }
        }

        $schema = new Schema($name, $schemaProperties);
        $this->schemas[$name] = $schema;

        return $schema;
    }

    public function createName($name)
    {
        return str_replace('\\', '.', $name);
    }
}
