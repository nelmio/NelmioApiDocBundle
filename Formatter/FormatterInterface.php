<?php

namespace Nelmio\ApiDocBundle\Formatter;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Route;

interface FormatterInterface
{
    /**
     * Format a collection of documentation data.
     *
     * @param array $collection
     * @return string|array
     */
    function format(array $collection);

    /**
     * Format documentation data for one route.
     *
     * @param ApiDoc $apiDoc
     * @param Route $route
     * return string|array
     */
    function formatOne(ApiDoc $apiDoc, Route $route);
}
