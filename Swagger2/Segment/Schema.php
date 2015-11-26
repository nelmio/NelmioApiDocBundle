<?php

namespace Nelmio\ApiDocBundle\Swagger2\Segment;

use Nelmio\ApiDocBundle\Swagger2\SegmentInterface;

class Schema implements SegmentInterface
{
    protected $name;

    protected $properties = array();

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function toArray()
    {

        $requiredProperties = array_filter($this->properties, function ($property) {
            return $property->isRequired();
        });

        $requiredNames = array_map(function ($property) {
            return $property->getName();
        }, $requiredParameters);

        $data = array(
            'type' => 'object',
            'required' => $requiredNames,
        );

        return $data;
    }
}
