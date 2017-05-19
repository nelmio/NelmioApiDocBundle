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
        'formData' => array(),
    );

    protected $methods = array('GET');

    protected $responses = array();

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function addParameter(AbstractParameter $parameter)
    {
        $in = $parameter->getIn();
        if (!isset($this->parameters[$in])) {
            throw new \Exception(sprintf('Invalid parameter type %s. Valid types: %s', $in, json_encode(array_keys($this->parameters))));
        }

        $this->parameters[$in][] = $parameter;
    }

    public function setMethods(array $methods)
    {
        $this->methods = array_map('strtolower', $methods);
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function getPathParameters()
    {
        $data = array();
        foreach ($this->parameters["path"] as $pathParam) {
            $data[] = $pathParam->toArray();
        }
        return $data;
    }

    private function getResponses()
    {
        return array();
    }

    public function toArray()
    {
        $data = array(
            "description" => $this->description,
            "responses" => $this->getResponses(),
        );

        return $data;
    }
}
