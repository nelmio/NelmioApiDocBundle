<?php

namespace Nelmio\ApiBundle\Formatter;

class SimpleFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    protected function renderOne(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function renderOne(array $collection)
    {
        return $collection;
    }
}
