<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Extractor;

use Nelmio\ApiDocBundle\Extractor\ApiDocExtractor;

class TestExtractor extends ApiDocExtractor
{
    public function __construct()
    {

    }

    public function getNormalization($input)
    {
        return $this->normalizeClassParameter($input);
    }
}
