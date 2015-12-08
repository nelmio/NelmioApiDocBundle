<?php

namespace Nelmio\ApiDocBundle\Swagger2\Segment\Parameter;

class SchemaProperty extends AbstractParameter
{
    public function __construct($name)
    {
        parent::__construct($name);
    }

    public function toArray()
    {
        $output = parent::toArray();
        unset($output["required"]);
        return $output;
    }
}
