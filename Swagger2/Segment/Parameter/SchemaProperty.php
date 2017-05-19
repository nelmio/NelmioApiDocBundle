<?php

namespace Nelmio\ApiDocBundle\Swagger2\Segment\Parameter;

use Nelmio\ApiDocBundle\Swagger2\Segment\Schema;

class SchemaProperty extends AbstractParameter
{
    protected $schema;

    protected $isCollection = false;

    public function __construct($name)
    {
        parent::__construct($name);
    }

    public function setCollection ($bool)
    {
        $this->isCollection = (boolean) $bool;
    }

    public function setSchema(Schema $schema)
    {
        $this->schema = $schema;
    }

    public function toArray()
    {
        $output = parent::toArray();
        unset($output["required"]);
        return $output;
    }
}
