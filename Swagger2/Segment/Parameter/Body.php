<?php

namespace Nelmio\ApiDocBundle\Swagger2\Segment\Parameter;

use Nelmio\ApiDocBundle\Swagger2\Segment\Schema;

class Body extends AbstractParameter
{
    protected $in = 'body';

    protected $schema;

    protected $isCollection = false;

    protected $collectionName = null;

    public function __construct($name) 
    {
        $this->name = $name;
        $this->required = true;
    }

    public function setSchema(Schema $schema)
    {
        $this->schema = $schema;
    }

    public function setCollection($boolean, $name = null)
    {
        $this->collectionName = $name;
        $this->isCollection = $boolean;
    }

    public function toArray()
    {
        return array(
            "name" => $this->name,
            "required" => true,
            "type" => $this->isCollection ? "array" : "object",
            "schema" => array(
                "\$ref" => $this->schema->getName(),
            )
        );
    }
}
