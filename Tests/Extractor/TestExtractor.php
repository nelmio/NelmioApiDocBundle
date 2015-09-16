<?php

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
