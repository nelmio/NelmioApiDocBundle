<?php

namespace Nelmio\ApiDocBundle\Swagger2\Segment;

use Nelmio\ApiDocBundle\Swagger2\Segment\Parameter\AbstractParameter;

class Path implements Segment
{
    protected $url;

    protected $parameters = array(
        'path' => array(),
        'query' => array(),
        'header' => array(),
        'body' => array(),
        'form' => array(),
    );

    public function __construct($url)
    {
        $this->url = $this->url;
    }

    public function addParameter(AbstractParameter $parameter)
    {
        if (!isset($this->parameters[$parameter->getType()])) {
            throw new \Exception(sprintf('Invalid parameter type %s. Valid types: %s', $parameter->getType(), json_encode(array_keys($this->parameters))));
        }

        $this->parameters[$parameter->getType()][] = $parameter;
    }
}
