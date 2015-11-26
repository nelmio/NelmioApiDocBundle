<?php

namespace Nelmio\ApiDocBundle\Swagger2\Segment\Parameter;

class Body extends AbstractParameter
{
    protected $in = 'body';

    public function __construct($name) 
    {
        $this->name = $name;
        $this->required = true;
    }
}
