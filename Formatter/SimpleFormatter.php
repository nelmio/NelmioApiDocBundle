<?php

namespace Nelmio\ApiBundle\Formatter;

class SimpleFormatter extends AbstractFormatter
{
    protected function render(array $data)
    {
        return $data;
    }
}
