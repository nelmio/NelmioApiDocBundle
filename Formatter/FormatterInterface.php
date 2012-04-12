<?php

namespace Nelmio\ApiBundle\Formatter;

use Nelmio\ApiBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Route;

interface FormatterInterface
{
    function format(array $collection);

    function formatOne(ApiDoc $apiDoc, Route $route);
}
