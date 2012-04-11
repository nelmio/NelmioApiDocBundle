<?php

namespace Nelmio\ApiBundle\Formatter;

use Nelmio\ApiBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Route;

interface FormatterInterface
{
    function format(ApiDoc $apiDoc, Route $route);
}
