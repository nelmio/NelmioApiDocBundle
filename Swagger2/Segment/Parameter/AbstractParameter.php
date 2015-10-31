<?php

namespace Nelmio\ApiDocBundle\Swagger2\Segment\Parameter;

use Nelmio\ApiDocBundle\Swagger2\Segment;

abstract class AbstractParameter implements Segment
{
    protected $name;

    protected $in;

    protected $description;

    protected $required;

    public function __construct($name)
    {
        $this->name = name;
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
            'in' => $this->in,
            'description' => $this->description,
            'required' => (boolean) $this->required,
        );
    }
}

