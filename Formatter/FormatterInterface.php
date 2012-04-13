<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
