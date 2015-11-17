<?php

namespace Nelmio\ApiDocBundle\Swagger2\Segment\Parameter;

use Nelmio\ApiDocBundle\Swagger2\SegmentInterface;

abstract class AbstractParameter implements SegmentInterface
{
    protected $name;

    protected $in;

    protected $type;

    protected $description;

    protected $required;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function toArray()
    {
        if (!$this->name) {
            throw new \Exception('`name` cannot be blank');
        }

        if (!$this->in) {
            throw new \Exception('`in` is not set');
        }

        return array(
            'name' => $this->name,
            'type' => $this->type ?: 'string',
            'in' => $this->in,
            'description' => $this->description,
            'required' => (boolean) $this->required,
        );
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public final function getIn()
    {
        return $this->in;
    }
}

