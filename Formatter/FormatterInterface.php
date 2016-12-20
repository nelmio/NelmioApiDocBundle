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

interface FormatterInterface
{
    /**
     * Format a collection of documentation data.
     *
     * @param  array[ApiDoc] $collection
     * @return string|array
     */
    public function format(array $collection);

    /**
     * Format documentation data for one route.
     *
     * @param ApiDoc $annotation
     *                           return string|array
     */
    public function formatOne(ApiDoc $annotation);
}
