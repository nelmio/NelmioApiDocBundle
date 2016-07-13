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
    private $swagger;
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
        if (null !== $this->swagger) {
            return $this->swagger;
        }

        $this->swagger = new Swagger();
        foreach ($this->extractors as $extractor) {
            $extractor->extractIn($this->swagger);
        }

        return $this->swagger;
    }
}
