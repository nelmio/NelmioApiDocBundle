<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Extractor;

use Nelmio\ApiDocBundle\Formatter\ApiDocSectionInterface;

interface ApiDocProviderInterface
{
    /**
     * This function will return an ApiDoc Section
     * @param  array $annotation
     * @return ApiDocSectionInterface
     */
    public function get($annotation);
}