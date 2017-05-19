<?php

namespace Nelmio\ApiDocBundle\Swagger2\Segment;

use Nelmio\ApiDocBundle\Swagger2\SegmentInterface;
use Nelmio\ApiDocBundle\Swagger2\Segment\Parameter\SchemaProperty;

class Schema implements SegmentInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var SchemaProperty[]
     */
    protected $properties = array();

    public function __construct($name, array $properties)
    {
        $this->name = $name;
        $this->properties = $properties;
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
