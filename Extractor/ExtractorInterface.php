<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\Extractor;

use gossi\swagger\Swagger;

interface ExtractorInterface
{
    public function extractIn(Swagger $api);
}
