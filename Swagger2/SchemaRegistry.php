<?php

namespace Nelmio\ApiDocBundle\Swagger2;

use Nelmio\ApiDocBundle\Swagger2\Segment\Schema;

class SchemaRegistry
{
    /**
     * @var array|Schema[]
     */
    protected $schemas = array();

    public function register($className, array $parameters)
    {
        $name = $this->createName($className);

        if (isset($this->schamas[$name])) {
            return $this->schemas[$name];
        }

        $schema = new Schema($name);

        $this->schemas[$name] = $schema;
        return $schema;
    }

    public function createName($name)
    {
        return str_replace('\\', '.', $name);
    }
}
