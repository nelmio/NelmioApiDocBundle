<?php

namespace Nelmio\ApiDocBundle\Swagger2\Segment\Parameter;

use Nelmio\ApiDocBundle\Swagger2\SegmentInterface;

abstract class AbstractParameter implements SegmentInterface
{
    protected $name;

    protected $in;

    protected $type;

    protected $description;

    protected $required = true;

    protected $enum = null;

    protected $default;

    protected $maximum;

    protected $minimum;

    protected $maxLength;

    protected $minLength;

    protected $pattern;

    protected $maxItems;
    
    protected $minItems;

    protected $uniqueItems;

    protected $multipleOf;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isRequired()
    {
        return (boolean) $this->required;
    }

    public function setEnum(array $enum = null)
    {
        $this->enum = $enum;
    }

    public function toArray()
    {
        if (!$this->name) {
            throw new \Exception('`name` cannot be blank');
        }

        if (!$this->in) {
            throw new \Exception('`in` is not set');
        }

        $array = array(
            'name' => $this->name,
            'type' => $this->type ?: 'string',
            'in' => $this->in,
            'description' => $this->description,
            'required' => (boolean) $this->required,
        );

        if (is_array($this->enum)) {
            $array["enum"] = $this->enum;
        }

        return $array;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public final function getIn()
    {
        return $this->in;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function setDefault($default)
    {
        $this->default = $default;
    }

    public function setMaximum($maximum)
    {
        $this->maximum = $maximum;
    }

    public function setMinimum($minimum)
    {
        $this->minimum = $minimum;
    }

    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
    }

    public function setMinLength($minLength)
    {
        $this->minLength = $minLength;
    }

    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
    }
    
    public function setMaxItems($maxItems)
    {
        $this->maxItems = $maxItems;
    }

    public function setMinItems($minItems)
    {
        $this->minItems = $minItems;
    }

    public function setUniqueItems($uniqueItems)
    {
        $this->minItems = $uniqueItems;
    }

    public function setMultipleOf($multipleOf)
    {
        $this->multipleOf = $multipleOf;
    }
}

