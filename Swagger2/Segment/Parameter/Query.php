<?php

namespace Nelmio\ApiDocBundle\Swagger2\Segment\Parameter;

class Query extends AbstractParameter
{
    protected $in = 'query';

    public function toArray()
    {
        $output = parent::toArray();
        return $output;
    }
}

