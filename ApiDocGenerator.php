<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle;

use EXSyst\Bundle\ApiDocBundle\Extractor\ExtractorInterface;
use gossi\swagger\Swagger;

class ApiDocGenerator
{
    private $extractors;

    /**
     * @param ExtractorInterface[] $extractors
     */
    public function __construct(array $extractors)
    {
        $this->extractors = $extractors;
    }

    /**
     * @return Swagger
     */
    public function extract()
    {
        $swagger = new Swagger();
        foreach ($this->extractors as $extractor) {
            $extractor->extractIn($swagger);
        }

        return $swagger;
    }
}
