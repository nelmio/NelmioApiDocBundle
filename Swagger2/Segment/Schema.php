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

        $required = array();
        $properties = array();

        foreach ($this->properties as $property) {
            if ($property->isRequired()) {
                $required[] = $property;
            } else {
                $properties[] = $property;
            }
        }

        $requiredNames = array_map(function ($property) {
            return $property->getName();
        }, $required);

        $data = array(
            'type' => 'object',
            'required' => $requiredNames,
        );

        return $data;
    }
}
