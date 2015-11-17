<?php

namespace Nelmio\ApiDocBundle\Swagger2\Segment\Parameter;

class Path extends AbstractParameter
{
    protected $in = 'path';

    public function __construct($name) 
    {
        $this->name = $name;
        $this->required = true;
    }
}
