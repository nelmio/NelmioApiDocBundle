<?php

namespace Nelmio\ApiDocBundle\Swagger2\Segment\Parameter;

class Query extends AbstractParameter
{
    protected $in = 'query';

    protected $default;

    protected $maximum;

    protected $minimum;

    protected $maxLength;

    protected $minLength;

    protected $pattern;

    protected $maxItems;
    
    protected $minItems;

    protected $uniqueItems;

    protected $enum;

    protected $multipleOf;

    protected $data;

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

    public function setEnum($enum)
    {
        $this->enum = $enum;
    }

    public function setMultipleOf($multipleOf)
    {
        $this->multipleOf = $multipleOf;
    }

    public function toArray()
    {
        $output = parent::toArray();
    }
}

