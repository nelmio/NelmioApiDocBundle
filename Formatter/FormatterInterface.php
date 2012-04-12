<?php

namespace Nelmio\ApiBundle\Formatter;

use Nelmio\ApiBundle\Annotation\ApiDoc;
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
