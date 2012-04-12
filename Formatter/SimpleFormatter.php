<?php

namespace Nelmio\ApiDocBundle\Formatter;

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
    protected function renderResourceSection($resource, array $arrayOfData)
    {
        return array($resource => $arrayOfData);
    }

    /**
     * {@inheritdoc}
     */
    protected function render(array $collection)
    {
        return $collection;
    }
}
