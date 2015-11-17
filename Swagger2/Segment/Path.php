<?php

namespace Nelmio\ApiDocBundle\Swagger2\Segment;

use Nelmio\ApiDocBundle\Swagger2\SegmentInterface;
use Nelmio\ApiDocBundle\Swagger2\Segment\Parameter\AbstractParameter;

class Path implements SegmentInterface
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
        $in = $parameter-getIn();
        if (!isset($this->parameters[$in])) {
            throw new \Exception(sprintf('Invalid parameter type %s. Valid types: %s', $in, json_encode(array_keys($this->parameters))));
        }

        $this->parameters[$in][] = $parameter;
    }

    public function toArray()
    {
        return array();
    }
}
